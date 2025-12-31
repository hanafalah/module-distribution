<?php

declare(strict_types=1);

namespace Hanafalah\ModuleDistribution;

use Hanafalah\LaravelSupport\Providers\BaseServiceProvider;

class ModuleDistributionServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     * 
     * @return $this
     */
    public function register()
    {
        $this->registerMainClass(ModuleDistribution::class)
            ->registerCommandService(Providers\CommandServiceProvider::class)
            ->registers([
                '*',
                'Services' => function () {
                    $this->binds([
                        Contracts\ModuleDistribution::class => new ModuleDistribution,
                        Contracts\Distribution::class => new Schemas\Distribution,
                        Contracts\Order::class => new Schemas\Order
                    ]);
                }
            ]);
    }

    /**
     * Get the base path of the package.
     *
     * @return string
     */
    protected function dir(): string
    {
        return __DIR__ . '/';
    }

    protected function migrationPath(string $path = ''): string
    {
        return database_path($path);
    }
}
