<?php

namespace Hanafalah\ModuleDistribution;

use Hanafalah\LaravelSupport\Supports\PackageManagement;

class ModuleDistribution extends PackageManagement implements Contracts\ModuleDistribution
{
    /** @var array */
    protected $__module_employee_config = [];

    /**
     * A description of the entire PHP function.
     *
     * @param Container $app The Container instance
     * @throws Exception description of exception
     * @return void
     */
    public function __construct()
    {
        $this->setConfig('module-distribution', $this->__module_employee_config);
    }
}
