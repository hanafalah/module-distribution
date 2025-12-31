<?php

use Hanafalah\ModuleDistribution\{
    Models as ModuleDistributionModels,
    Commands as ModuleDistributionCommand,
    Contracts
};

return [
    'commands' => [
        ModuleDistributionCommand\InstallMakeCommand::class
    ],
    'contracts'                => [
        'distribution'         => Contracts\Distribution::class,
        'module_distribution'  => Contracts\ModuleDistribution::class,
        'order'                => Contracts\Order::class
    ],
    'database'                 => [
        'models'               => [
            'Order'            => ModuleDistributionModels\Distribution\Order::class,
            'Distribution'     => ModuleDistributionModels\Distribution\Distribution::class
        ]
    ]
];
