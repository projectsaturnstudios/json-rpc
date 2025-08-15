<?php

namespace ProjectSaturnStudios\RpcServer;

use Closure;
use LogicException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Illuminate\Routing\RouteAction;
use Illuminate\Support\Collection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\CompiledRoute;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Symfony\Component\Routing\Route as SymfonyRoute;
use ProjectSaturnStudios\RpcServer\Routing\ProcedureDispatcher;
use ProjectSaturnStudios\RpcServer\Routing\Matching\MethodValidator;
use ProjectSaturnStudios\RpcServer\Exceptions\JsonRpcResponseException;
use ProjectSaturnStudios\RpcServer\Routing\ProcedureCallParameterBinder;
use ProjectSaturnStudios\RpcServer\Interfaces\CallableDispatcherContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;
use Illuminate\Routing\FiltersControllerMiddleware as FiltersProcedureMiddleware;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureDispatcherContract as ProcedureDispatcherContract;

class RemoteProcedureCall
{
    use FiltersProcedureMiddleware;

    protected ?Container $app = null;
    protected ?RpcRouter $router = null;

    public mixed $procedure = null;
    protected array $action = [];
    protected array $wheres = [];
    protected array $middleware = [];
    protected ?string $prefix = null;
    public bool $isFallback = false;
    public ?array $parameters = null;
    public array $defaults = [];
    public ?array $parameterNames = null;
    public ?array $originalParameters = null;
    public static array $validators = [];
    public ?array $computedMiddleware = null;
    public CompiledRoute|false $compiled = false;

    public function __construct(
        protected string $method,
        protected readonly array|string|Closure|null $act = null,
    ) {

        $this->action = Arr::except($this->parseAction($act), ['prefix']);
        $this->prefix(is_array($act) ? Arr::get($act, 'prefix') : '');
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getAction(?string $key = null): array|string|Closure|null
    {
        return Arr::get($this->action, $key);
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function setContainer(Container $app): static
    {
        $this->app = $app;
        return $this;
    }

    public function setRouter(RpcRouter $router): static
    {
        $this->router = $router;
        return $this;
    }

    public function prefix(?string $prefix): static
    {
        $prefix ??= '';

        $this->updatePrefixOnAction($prefix);

        $method = rtrim($prefix, '/').'/'.ltrim($this->method, '/');

        return $this->setMethod($method !== '/' ? trim($method, '/') : $method);
    }

    protected function updatePrefixOnAction(?string $prefix): void
    {
        if (! empty($newPrefix = trim(rtrim($prefix, '/').'/'.ltrim($this->action['prefix'] ?? '', '/'), '/'))) {
            $this->action['prefix'] = $newPrefix;
        }
    }

    public function setAction(array $action): static
    {
        $this->action = $action;
        return $this;
    }

    protected function parseAction(array|callable|null $action): array
    {
        return RouteAction::parse($this->method, $action);
    }

    public function where(array|string $name, ?string $expression = null): static
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;
        }

        return $this;
    }

    protected function parseWhere(array|string $name, ?string $expression): array
    {
        return is_array($name) ? $name : [$name => $expression];
    }

    public function matches(ProcedureCallRequestContract $request, bool $includingMethod = true): bool
    {
        $this->compileRoute();

        foreach (self::getValidators() as $validator) {
            if (! $includingMethod && $validator instanceof MethodValidator) {
                continue;
            }

            if (! $validator->matches($this, $request)) {
                return false;
            }
        }

        return true;
    }

    protected function compileRoute(): CompiledRoute
    {
        if (! $this->compiled) {
            $this->compiled = $this->toSymfonyRoute()->compile();
        }

        return $this->compiled;
    }

    public function toSymfonyRoute(): Route
    {
        return new SymfonyRoute(
            preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->getMethod()), $this->getOptionalParameterNames(),
            $this->wheres, ['utf8' => true],
            '', [], []
        );
    }

    public function getOptionalParameterNames(): array
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->getMethod(), $matches);

        return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
    }

    public function getCompiled(): CompiledRoute
    {
        return $this->compiled;
    }

    public function bind(ProcedureCallRequestContract $request): static
    {
        $this->compileRoute();

        $this->parameters = (new ProcedureCallParameterBinder($this))
            ->parameters($request);

        $this->originalParameters = $this->parameters;

        return $this;
    }

    public function parameterNames(): array
    {
        if (isset($this->parameterNames)) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParameterNames();
    }

    protected function compileParameterNames(): array
    {
        preg_match_all('/\{(.*?)\}/', $this->method, $matches);

        return array_map(fn ($m) => trim($m, '?'), $matches[1]);
    }

    public static function getValidators(): array
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }


        return static::$validators = [
            new MethodValidator,
        ];
    }

    public function middleware($middleware = null): array|static
    {
        if (is_null($middleware)) {
            return (array) ($this->action['middleware'] ?? []);
        }

        if (! is_array($middleware)) {
            $middleware = func_get_args();
        }

        foreach ($middleware as $index => $value) {
            $middleware[$index] = (string) $value;
        }

        $this->action['middleware'] = array_merge(
            (array) ($this->action['middleware'] ?? []), $middleware
        );

        return $this;
    }

    public function gatherMiddleware(): array
    {
        if (! is_null($this->computedMiddleware)) {
            return $this->computedMiddleware;
        }

        $this->computedMiddleware = [];

        return $this->computedMiddleware = RpcRouter::uniqueMiddleware(array_merge(
            $this->middleware(), $this->procedureMiddleware()
        ));
    }

    public function procedureMiddleware()
    {
        if (! $this->isProcedureAction()) {
            return [];
        }

        [$controllerClass, $controllerMethod] = [
            $this->getProcedureClass(),
            $this->getProcedureMethod(),
        ];

        if (is_a($controllerClass, HasMiddleware::class, true)) {
            return $this->staticallyProvidedProcedureMiddleware(
                $controllerClass, $controllerMethod
            );
        }

        if (method_exists($controllerClass, 'getMiddleware')) {
            return $this->procedureDispatcher()->getMiddleware(
                $this->getProcedure(), $controllerMethod
            );
        }

        return [];
    }

    public function parameters(): array
    {
        if (isset($this->parameters)) {
            return $this->parameters;
        }

        throw new LogicException('Procedure Call is not bound.');
    }

    public function parametersWithoutNulls(): array
    {
        return array_filter($this->parameters(), fn ($p) => ! is_null($p));
    }


    public function procedureDispatcher()
    {
        if ($this->app->bound(ProcedureDispatcherContract::class)) {
            return $this->app->make(ProcedureDispatcherContract::class);
        }

        return new ProcedureDispatcher($this->app);
    }

    public function getProcedure()
    {
        if (! $this->isProcedureAction()) {
            return null;
        }

        if (! $this->procedure) {
            $class = $this->getProcedureClass();

            $this->procedure = $this->app->make(ltrim($class, '\\'));
        }

        return $this->procedure;
    }

    /**
     * Get the statically provided procedure middleware for the given class and method.
     *
     * @param  string  $class
     * @param  string  $method
     * @return array
     */
    protected function staticallyProvidedProcedureMiddleware(string $class, string $method): array
    {
        return (new Collection($class::middleware()))
            ->map(function ($middleware) {
                return $middleware instanceof Middleware
                    ? $middleware
                    : new Middleware($middleware);
            })
            ->reject(function ($middleware) use ($method) {
                return static::methodExcludedByOptions(
                    $method, ['only' => $middleware->only, 'except' => $middleware->except],
                );
            })
            ->map
            ->middleware
            ->flatten()
            ->values()
            ->all();
    }

    protected function getProcedureMethod(): string
    {
        return $this->parseProcedureCallback()[1];
    }

    protected function isProcedureAction(): bool
    {
        return is_string($this->action['uses']) && ! $this->isSerializedClosure();
    }

    protected function isSerializedClosure(): bool
    {
        return RouteAction::containsSerializedClosure($this->action);
    }

    public function getProcedureClass(): ?string
    {
        return $this->isProcedureAction() ? $this->parseProcedureCallback()[0] : null;
    }

    protected function parseProcedureCallback(): ?array
    {
        return Str::parseCallback($this->action['uses']);
    }

    public function excludedMiddleware(): array
    {
        return (array) ($this->action['excluded_middleware'] ?? []);
    }

    public function run(): ProcedureCallResultContract
    {
        $this->app = $this->app ?: new Container;

        try {
            if ($this->isProcedureAction()) {
                return $this->runProcedure();
            }

            return $this->runCallable();
        } catch (JsonRpcResponseException $e) {
            return $e->getResponse();
        }
    }

    protected function runProcedure(): mixed
    {
        return $this->procedureDispatcher()->dispatch(
            $this, $this->getProcedure(), $this->getProcedureMethod()
        );
    }

    protected function runCallable()
    {
        $callable = $this->action['uses'];

        if ($this->isSerializedClosure()) {
            $callable = unserialize($this->action['uses'])->getClosure();
        }

        return $this->app[CallableDispatcherContract::class]->dispatch($this, $callable);
    }
}

