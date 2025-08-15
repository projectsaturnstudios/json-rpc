<?php

namespace ProjectSaturnStudios\RpcServer\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('make:procedure', "Creates a new Remote Procedure Call (RPC) controller")]
class MakeProcedureControllerCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:procedure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new RPC procedure controller';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Procedure';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        $this->components->info(sprintf('%s [%s] created successfully.', $this->type, $this->getNameInput()));

        return true;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['{{ namespace }}', '{{namespace}}'],
            $this->getNamespace($name),
            $stub
        );

        $stub = str_replace(
            ['{{ class }}', '{{class}}'],
            $this->getClassName($name),
            $stub
        );

        return $this;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the class name for the stub.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        return str_replace($this->getNamespace($name).'\\', '', $name);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Rpc\\Procedures';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        // Always put procedures in app/Rpc/Procedures directory
        $className = class_basename($name);
        
        return $this->laravel['path'].'/Rpc/Procedures/'.$className.'.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/procedure.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../../..'.$stub;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the procedure controller'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the procedure already exists'],
        ];
    }
}
