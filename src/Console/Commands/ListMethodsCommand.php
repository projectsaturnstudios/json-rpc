<?php

namespace JSONRPC\Console\Commands;

use Illuminate\Console\Command;
use JSONRPC\Support\Facades\RPCRouter;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('method:list', "Lists all registered JSON-RPC methods")]
class ListMethodsCommand extends Command
{
    /**
     * @return void
     */
    public function handle(): int
    {
        $methods = RPCRouter::getMethods();

        if (empty($methods)) {
            $this->info('No RPC methods registered.');
            return 0;
        }

        $this->newLine();

        // Prepare data for table display
        $tableData = [];
        foreach ($methods as $method_name => $controller_instance) {
            $tableData[] = [
                'method' => $method_name,
                'controller' => get_class($controller_instance)
            ];
        }

        // Calculate column widths
        $maxMethodLength = max(array_map(fn($item) => strlen($item['method']), $tableData));
        $maxMethodLength = max($maxMethodLength, 6); // Minimum width for "METHOD" header

        $maxControllerLength = max(array_map(fn($item) => strlen($item['controller']), $tableData));
        $maxControllerLength = max($maxControllerLength, 10); // Minimum width for "CONTROLLER" header

        // Total width calculation (similar to Laravel's approach)
        $totalWidth = $maxMethodLength + $maxControllerLength + 20; // Extra space for dots and padding

        // Display each method
        foreach ($tableData as $item) {
            $method = str_pad($item['method'], $maxMethodLength);
            $controller = $item['controller'];

            // Calculate dots needed
            $dotsNeeded = $totalWidth - strlen($method) - strlen($controller) - 2;
            $dots = str_repeat('.', max(1, $dotsNeeded));

            $this->line("  <fg=yellow>{$method}</fg=yellow> {$dots} <fg=blue>{$controller}</fg=blue>");
        }

        $this->newLine();
        $this->newLine();

        // Summary line (like Laravel's format)
        $methodCount = count($methods);
        $this->line("        <fg=green>Showing [{$methodCount}] RPC methods</fg=green>");

        $this->newLine();
        $this->newLine();

        return 0;
    }
}
