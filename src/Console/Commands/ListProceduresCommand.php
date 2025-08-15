<?php

namespace ProjectSaturnStudios\RpcServer\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Stringable;
use Illuminate\Support\Collection;
use ProjectSaturnStudios\RpcServer\RemoteProcedureCall;
use Symfony\Component\Console\Terminal;
use ProjectSaturnStudios\RpcServer\RpcRouter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('procedure:list', "Lists all registered Remote Procedure Calls (RPCs)")]
class ListProceduresCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'procedure:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered RPC procedures';

    /**
     * The table headers for the command.
     *
     * @var string[]
     */
    protected $headers = ['Method', 'Name', 'Action'];

    /**
     * The terminal width resolver callback.
     *
     * @var \Closure|null
     */
    protected static $terminalWidthResolver;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $router = resolve(RpcRouter::class);

        if (! count($router->getProcedureCalls()->getProcedureCalls())) {
            return $this->components->error("Your application doesn't have any RPC procedures.");
        }

        if (empty($procedures = $this->getProcedures())) {
            return $this->components->error("Your application doesn't have any procedures matching the given criteria.");
        }

        $this->displayProcedures($procedures);
    }

    /**
     * Compile the procedures into a displayable format.
     *
     * @return array
     */
    protected function getProcedures()
    {
        $router = resolve(RpcRouter::class);

        $procedures = (new Collection($router->getProcedureCalls()->getProcedureCalls()))
            ->map(fn ($procedure) => $this->getProcedureInformation($procedure))
            ->filter()
            ->all();

        if (($sort = $this->option('sort')) !== null) {
            $procedures = $this->sortProcedures($sort, $procedures);
        } else {
            $procedures = $this->sortProcedures('method', $procedures);
        }

        if ($this->option('reverse')) {
            $procedures = array_reverse($procedures);
        }

        return $this->pluckColumns($procedures);
    }

    /**
     * Get the procedure information for a given procedure.
     *
     * @param  \ProjectSaturnStudios\RpcServer\RemoteProcedureCall  $procedure
     * @return array
     */
    protected function getProcedureInformation($procedure)
    {
        $action = $this->formatActionFromProcedure($procedure);

        return $this->filterProcedure([
            'method' => $procedure->getMethod() ?? 'unknown',
            'name' => $this->extractNameFromProcedure($procedure),
            'action' => $action,
            'middleware' => $this->getMiddleware($procedure),
        ]);
    }

    /**
     * Extract the name/prefix from a procedure.
     *
     * @param  mixed  $procedure
     * @return string
     */
    protected function extractNameFromProcedure($procedure): string
    {
        // Try different methods that might exist on the procedure
        if (method_exists($procedure, 'getPrefix')) {
            return $procedure->getPrefix() ?? '';
        }

        if (method_exists($procedure, 'getName')) {
            return $procedure->getName() ?? '';
        }

        // Try to get from action array
        if (method_exists($procedure, 'getAction')) {
            $action = $procedure->getAction();
            if (is_array($action) && isset($action['prefix'])) {
                return $action['prefix'];
            }
        }

        // Try direct property access
        if (isset($procedure->prefix)) {
            return $procedure->prefix ?? '';
        }

        if (isset($procedure->name)) {
            return $procedure->name ?? '';
        }

        return '';
    }

    /**
     * Filter the procedure to remove any unnecessary data.
     *
     * @param  array  $procedure
     * @return array|null
     */
    protected function filterProcedure(array $procedure)
    {
        if (($this->option('name') && ! Str::contains($procedure['name'], $this->option('name'))) ||
            ($this->option('method') && ! Str::contains($procedure['method'], $this->option('method')))) {
            return null;
        }

        return $procedure;
    }

    /**
     * Sort the procedures by a given element.
     *
     * @param  string  $sort
     * @param  array  $procedures
     * @return array
     */
    protected function sortProcedures($sort, array $procedures)
    {
        if ($sort === 'definition') {
            return $procedures;
        }

        if (Str::contains($sort, ',')) {
            $sort = explode(',', $sort);
        }

        return (new Collection($procedures))
            ->sortBy($sort)
            ->toArray();
    }

    /**
     * Remove unnecessary columns from the procedures.
     *
     * @param  array  $procedures
     * @return array
     */
    protected function pluckColumns(array $procedures)
    {
        // Keep all columns for display logic, but note which ones are selected
        return $procedures;
    }

    /**
     * Display the procedure information on the console.
     *
     * @param  array  $procedures
     * @return void
     */
    protected function displayProcedures(array $procedures)
    {
        $procedures = new Collection($procedures);

        $this->output->writeln(
            $this->option('json') ? $this->asJson($procedures) : $this->forCli($procedures)
        );
    }

    /**
     * Convert the given procedures to a CLI output.
     *
     * @param  \Illuminate\Support\Collection  $procedures
     * @return string
     */
    protected function forCli($procedures)
    {
        $procedures = $procedures->map(
            fn ($procedure) => array_merge($procedure, [
                'action' => $this->formatActionForCli($procedure),
            ])
        );

        $maxMethod = mb_strlen($procedures->max('method'));

        $terminalWidth = $this->getTerminalWidth();

        $procedureCount = $this->determineProcedureCountOutput($procedures, $terminalWidth);

        return $procedures->map(function ($procedure) use ($maxMethod, $terminalWidth) {
            $action = $procedure['action'] ?? '';
            $method = $procedure['method'] ?? '';
            $name = $procedure['name'] ?? '';
            $middleware = $procedure['middleware'] ?? '';

            $middlewareDisplay = '';
            if (!empty($middleware) && in_array('middleware', $this->getColumns())) {
                $middlewareDisplay = (new Stringable($middleware))->explode("\n")->filter()->whenNotEmpty(
                    fn ($collection) => $collection->map(
                        fn ($middleware) => sprintf('         %s⇂ %s', str_repeat(' ', $maxMethod), $middleware)
                    )
                )->implode("\n");
            }

            $method = str_pad($method, $maxMethod);

            $dots = str_repeat('.', max(
                ($terminalWidth - mb_strlen($method.$action.$name) - 8), 0
            ));

            $result = [
                sprintf('  <fg=yellow>%s</fg=yellow> %s%s <fg=blue>%s</fg=blue>',
                    $method,
                    $name ? "<fg=cyan>{$name}</fg=cyan> " : '',
                    $dots,
                    $action
                )
            ];

            if (!empty($middlewareDisplay)) {
                $result[] = $middlewareDisplay;
            }

            return $result;
        })->flatten()->filter()->prepend('')->push('')->push($procedureCount)->implode("\n");
    }

    /**
     * Determine and return the output for displaying the number of procedures.
     *
     * @param  \Illuminate\Support\Collection  $procedures
     * @param  int  $terminalWidth
     * @return string
     */
    protected function determineProcedureCountOutput($procedures, $terminalWidth)
    {
        $procedureCount = $procedures->count();

        if ($procedureCount === 0) {
            return '';
        }

        $text = sprintf('Showing [%d] %s', $procedureCount, Str::plural('procedure', $procedureCount));

        $dots = str_repeat(' ', max($terminalWidth - mb_strlen($text) - 2, 0));

        return sprintf('  <fg=green>%s</fg=green>%s', $text, $dots);
    }

    /**
     * Get the formatted action for display on the CLI.
     *
     * @param  array  $procedure
     * @return string
     */
    protected function formatActionForCli($procedure)
    {
        $action = $procedure['action'] ?? '';

        if (! $action) {
            return 'Closure';
        }

        $rootControllerNamespace = $this->laravel->getNamespace() . 'Http\\Controllers\\';

        if (Str::startsWith($action, $rootControllerNamespace)) {
            return substr($action, strlen($rootControllerNamespace));
        }

        $rootNamespace = $this->laravel->getNamespace();

        if (Str::startsWith($action, $rootNamespace)) {
            return substr($action, strlen($rootNamespace));
        }

        return $action;
    }

    /**
     * Get the middleware for the procedure.
     *
     * @param  \ProjectSaturnStudios\RpcServer\RemoteProcedureCall  $procedure
     * @return string
     */
    protected function getMiddleware(RemoteProcedureCall $procedure)
    {
        $middlewares = [];

        // Try different ways to get middleware
        if (method_exists($procedure, 'getMiddleware')) {
            $middlewares = $procedure->getMiddleware() ?? [];
        } elseif (method_exists('middleware', $procedure::class)) {
            $middlewares = $procedure->middleware() ?? [];
        }

        return (new Collection($middlewares))
            ->map(fn ($middleware) => $middleware instanceof \Closure ? 'Closure' : $middleware)
            ->implode("\n");
    }

    /**
     * Format the action string from a RemoteProcedureCall.
     *
     * @param  \ProjectSaturnStudios\RpcServer\RemoteProcedureCall  $procedure
     * @return string
     */
    protected function formatActionFromProcedure($procedure): string
    {
        if (method_exists($procedure, 'getAction')) {
            $action = $procedure->getAction();
        } elseif (method_exists($procedure, 'getActionName')) {
            $action = $procedure->getActionName();
        } else {
            // Fallback - try to get the action from the object properties
            $reflection = new \ReflectionClass($procedure);

            // Try common property names
            foreach (['action', 'controller', 'uses'] as $property) {
                try {
                    if ($reflection->hasProperty($property)) {
                        $prop = $reflection->getProperty($property);
                        if ($prop->isPublic()) {
                            $action = $procedure->$property;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    // Continue to next property
                }
            }

            if (!isset($action)) {
                $action = 'Unknown';
            }
        }

        if (is_array($action)) {
            $action = $action['uses'] ?? $action[0] ?? 'Array Action';
        }

        if ($action instanceof \Closure) {
            return 'Closure';
        }

        return ltrim((string) $action, '\\');
    }

    /**
     * Convert the given procedures to a JSON output.
     *
     * @param  \Illuminate\Support\Collection  $procedures
     * @return string
     */
    protected function asJson($procedures)
    {
        return $procedures->map(function ($procedure) {
            return Arr::except($procedure, ['action']);
        })->values()->toJson();
    }

    /**
     * Get the table headers for the visible columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $availableColumns = ['method', 'name', 'action', 'middleware'];

        if ($this->option('columns')) {
            return array_intersect($availableColumns, $this->option('columns'));
        }

        // Default columns exclude middleware
        return ['method', 'name', 'action'];
    }

    /**
     * Get the terminal width.
     *
     * @return int
     */
    protected function getTerminalWidth()
    {
        return is_callable(static::$terminalWidthResolver)
            ? call_user_func(static::$terminalWidthResolver)
            : (new Terminal)->getWidth();
    }

    /**
     * Set a callback that should be used when resolving the terminal width.
     *
     * @param  \Closure|null  $resolver
     * @return void
     */
    public static function resolveTerminalWidthUsing($resolver)
    {
        static::$terminalWidthResolver = $resolver;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['columns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Columns to include in the output (method, name, action, middleware)'],
            ['json', null, InputOption::VALUE_NONE, 'Output the procedures as JSON'],
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the procedures by method'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the procedures by name'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the procedures'],
            ['sort', null, InputOption::VALUE_OPTIONAL, 'The column (method, name, action, middleware) to sort by', 'method'],
        ];
    }
}
