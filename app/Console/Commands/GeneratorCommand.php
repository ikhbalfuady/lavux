<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ModuleGenerator;

class GeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lavux-generate {--scope= : The scope of the generated file} {--s= : The scope of the generated file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate base module from generator scope list, access : "/generator" in frontend to make module list.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $scope = $this->option('scope') ?? $this->option('s');
        ModuleGenerator::generate($this, $scope);

    }
}
