<?php

namespace ProjectSaturnStudios\RpcServer;

use Closure;
use Illuminate\Routing\SortedMiddleware;
use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Routing\RouteGroup;
use Illuminate\Container\Container;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Routing\MiddlewareNameResolver;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallResultContract;
use ProjectSaturnStudios\RpcServer\Routing\ProcedureCollection;
use ProjectSaturnStudios\RpcServer\Routing\ProcedureCallFileRegistrar;
use ProjectSaturnStudios\RpcServer\Interfaces\ProcedureCallRequestContract;

#[Singleton]
class RpcRouter
{
    protected array $group_stack = [];
    protected array $patterns = [];

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected array $middleware = [];

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected array $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    public array $middlewarePriority = [];

    protected ?RemoteProcedureCall $current = null;
    protected ?ProcedureCallRequestContract $currentRequest = null;

    public function __construct(
        protected Container $app,
        protected ProcedureCollection $procedure_calls = new ProcedureCollection(),
    ) {}

    public function registerProcedureCall(string $method, array|string|callable|null $action = null): RemoteProcedureCall
    {
        return $this->procedure_calls->add($this->createRPC($method, $action));
    }

    public function mergeWithLastGroup($new, $prependExistingPrefix = true): array
    {
        return RouteGroup::merge($new, end($this->group_stack), $prependExistingPrefix);
    }

    public function newProcedureCall($uri, $action): RemoteProcedureCall
    {
        return (new RemoteProcedureCall($uri, $action))
            ->setRouter($this)
            ->setContainer($this->app);
    }

    public function getLastGroupPrefix()
    {
        if ($this->hasGroupStack()) {
            $last = end($this->group_stack);

            return $last['prefix'] ?? '';
        }

        return '';
    }

    public function hasGroupStack(): bool
    {
        return ! empty($this->group_stack);
    }

    public function getProcedureCalls(): ProcedureCollection
    {
        return $this->procedure_calls;
    }

    protected function createRPC(string $method, array|string|callable|null $action = null): RemoteProcedureCall
    {
        if ($this->actionReferencesProcedure($action)) $action = $this->convertToProcedureAction($action);
        $procedure_call = $this->newProcedureCall($this->prefix($method), $action);
        if ($this->hasGroupStack()) $this->mergeGroupAttributesIntoProcedureCall($procedure_call);
        $this->addWhereClausesToProcedureCall($procedure_call);

        return $procedure_call;
    }

    protected function addWhereClausesToProcedureCall(RemoteProcedureCall $procedure_call): RemoteProcedureCall
    {
        $procedure_call->where(array_merge(
            $this->patterns, $procedure_call->getAction()['where'] ?? []
        ));

        return $procedure_call;
    }

    protected function mergeGroupAttributesIntoProcedureCall(RemoteProcedureCall $procedure_call): void
    {
        $procedure_call->setAction($this->mergeWithLastGroup(
            $procedure_call->getAction(),
            prependExistingPrefix: false
        ));
    }

    protected function convertToProcedureAction($action)
    {
        if (is_string($action)) $action = ['uses' => $action];

        if ($this->hasGroupStack()) {
            $action['uses'] = $this->prependGroupProcedure($action['uses']);
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }
        $action['procedure'] = $action['uses'];

        return $action;
    }

    protected function prependGroupNamespace($class)
    {
        $group = end($this->group_stack);

        return isset($group['namespace']) && ! str_starts_with($class, '\\') && ! str_starts_with($class, $group['namespace'])
            ? $group['namespace'].'\\'.$class
            : $class;
    }

    protected function prependGroupProcedure($class)
    {
        $group = end($this->group_stack);

        if (! isset($group['procedure'])) {
            return $class;
        }

        if (class_exists($class)) {
            return $class;
        }

        if (str_contains($class, '@')) {
            return $class;
        }

        return $group['procedure'].'@'.$class;
    }

    protected function actionReferencesProcedure($action): bool
    {
        if (! $action instanceof Closure) {
            return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
        }

        return false;
    }

    protected function prefix(string $method): string
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($method, '/'), '/') ?: '/';
    }

    public function group(array $attributes, $routes): static
    {
        foreach (Arr::wrap($routes) as $groupRoutes) {
            $this->updateGroupStack($attributes);

            // Once we have updated the group stack, we'll load the provided routes and
            // merge in the group's attributes when the routes are created. After we
            // have created the routes, we will pop the attributes off the stack.
            $this->loadProcedureCalls($groupRoutes);

            array_pop($this->group_stack);
        }

        return $this;
    }

    protected function loadProcedureCalls($routes): void
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            (new ProcedureCallFileRegistrar($this))->register($routes);
        }
    }

    protected function updateGroupStack(array $attributes): void
    {
        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->group_stack[] = $attributes;
    }

    protected function findProcedureCall(ProcedureCallRequestContract $request): RemoteProcedureCall
    {
        $this->current = $procedure_call = $this->procedure_calls->match($request);
        $procedure_call->setContainer($this->app);
        $this->app->instance(RemoteProcedureCall::class, $procedure_call);

        return $procedure_call;
    }

    public function dispatch(ProcedureCallRequestContract $request): ProcedureCallResultContract
    {
        $this->currentRequest = $request;
        // Make the current RPC request injectable for closures / controllers
        $this->app->instance(ProcedureCallRequestContract::class, $request);

        return $this->dispatchToProcedureCall($request);
    }

    public function dispatchToProcedureCall(ProcedureCallRequestContract $request): ProcedureCallResultContract
    {
        return $this->runProcedureCall($request, $this->findProcedureCall($request));
    }

    protected function runProcedureCall(ProcedureCallRequestContract $request, RemoteProcedureCall $procedure_call): ProcedureCallResultContract
    {
        $request->setRouteResolver(fn () => $procedure_call);

        // @todo - allow for evented-hooks
        //$this->events->dispatch(new ProcedureCallMatched($procedure_call, $request));

        return $this->prepareResponse($request,
            $this->runProcedureCallWithinStack($procedure_call, $request)
        );
    }

    protected function runProcedureCallWithinStack(RemoteProcedureCall $procedure_call, ProcedureCallRequestContract $request)
    {
        $shouldSkipMiddleware = $this->app->bound('middleware.disable') &&
            $this->app->make('middleware.disable') === true;

        $middleware = $shouldSkipMiddleware ? [] : $this->gatherProcedureCallMiddleware($procedure_call);

        return (new Pipeline($this->app))
            ->send($request)
            ->through($middleware)
            ->then(function (ProcedureCallRequestContract $request) use ($procedure_call) {
                // Ensure DI resolves the current RPC request instance
                $this->app->instance(ProcedureCallRequestContract::class, $request);
                return $procedure_call->run();
            });
    }

    public function prepareResponse(ProcedureCallRequestContract $request, ProcedureCallResultContract $response): ProcedureCallResultContract
    {
        //$this->events->dispatch(new PreparingResponse($request, $response));

        return tap(static::toResponse($request, $response), function ($response) use ($request) {
            //$this->events->dispatch(new ResponsePrepared($request, $response));
        });
    }

    public function gatherProcedureCallMiddleware(RemoteProcedureCall $procedure_call): array
    {
        return $this->resolveMiddleware($procedure_call->gatherMiddleware(), $procedure_call->excludedMiddleware());
    }

    public function resolveMiddleware(array $middleware, array $excluded = []): array
    {
        $excluded = $excluded === []
            ? $excluded
            : (new Collection($excluded))
                ->map(fn ($name) => (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups))
                ->flatten()
                ->values()
                ->all();

        $middleware = (new Collection($middleware))
            ->map(fn ($name) => (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups))
            ->flatten()
            ->when(
                ! empty($excluded),
                fn ($collection) => $collection->reject(function ($name) use ($excluded) {
                    if ($name instanceof Closure) {
                        return false;
                    }

                    if (in_array($name, $excluded, true)) {
                        return true;
                    }

                    if (! class_exists($name)) {
                        return false;
                    }

                    $reflection = new ReflectionClass($name);

                    return (new Collection($excluded))->contains(
                        fn ($exclude) => class_exists($exclude) && $reflection->isSubclassOf($exclude)
                    );
                })
            )
            ->values();

        return $this->sortMiddleware($middleware);
    }

    protected function sortMiddleware(Collection $middlewares): array
    {
        return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
    }

    public static function toResponse(ProcedureCallRequestContract $request, ProcedureCallResultContract $response): ProcedureCallResultContract
    {
        return $response;
    }

    public static function uniqueMiddleware(array $middleware): array
    {
        $seen = [];
        $result = [];

        foreach ($middleware as $value) {
            $key = \is_object($value) ? \spl_object_id($value) : $value;

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = $value;
            }
        }

        return $result;
    }

    public static function boot(): void
    {
        app()->singleton(
            static::class,
            fn (Container $app) => new static(
                $app,
                $app->make(ProcedureCollection::class)
            )
        );
    }
}
