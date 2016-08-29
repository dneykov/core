<?php

namespace Hesto\Core\Commands;

use Hesto\Core\Traits\CanReplaceKeywords;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


abstract class AppendContentCommand extends InstallCommand
{
    use CanReplaceKeywords;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Get the destination path.
     *
     * @return string
     */
    abstract function getSettings();

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $settings = $this->getSettings();

        foreach ($settings as $setting) {
            $path = $setting['path'];
            $fullPath = base_path() . $path;

            if($setting['put']) {
                if ($this->putContent($fullPath, $this->compileContent($fullPath, $setting))) {
                    $this->getInfoMessage($fullPath);
                }

                continue;
            }

            if($setting['append']) {
                if ($this->appendContent($fullPath, $this->compileContent($fullPath, $setting))) {
                    $this->getInfoMessage($fullPath);
                }

                continue;
            }

        }

        return true;
    }

    /**
     * Compile content.
     *
     * @param $content
     * @return mixed
     */
    protected function compileContent($path, $setting)
    {
        $content = $this->replaceNames($this->files->get($setting['stub']));

        if($setting['search']) {
            if ($setting['prefix']) {
                $stub = $content . $setting['search'];
            } else {
                $stub = $setting['search'] . $content;
            }

            $content = str_replace($setting['search'], $stub, $this->files->get($path));
        }

        return $content;
    }

    protected function getInfoMessage($filePath)
    {
        return $this->info('Content changed in: ' . $filePath);
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
     * Get the console command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force override existing files'],
        ];
    }
}
