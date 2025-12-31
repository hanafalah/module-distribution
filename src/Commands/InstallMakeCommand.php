<?php

namespace Hanafalah\ModuleDistribution\Commands;

class InstallMakeCommand extends EnvironmentCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module-distribution:install';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command ini digunakan untuk installing awal module distribution';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = 'Hanafalah\ModuleDistribution\ModuleDistributionServiceProvider';

        $this->comment('Installing Module Distribution...');
        $this->callSilent('vendor:publish', [
            '--provider' => $provider,
            '--tag'      => 'config'
        ]);
        $this->info('✔️  Created config/module-distribution.php');

        $this->callSilent('vendor:publish', [
            '--provider' => $provider,
            '--tag'      => 'migrations'
        ]);
        $this->info('✔️  Created migrations');

        $this->comment('hanafalah/module-distribution installed successfully.');
    }
}
