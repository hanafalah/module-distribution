<?php

namespace Hanafalah\ModuleDistribution\Enums\Distribution;

enum Status: string
{
    case DRAFT         = 'DRAFT';
    case ORDERED       = 'ORDERED';
    case DISTRIBUTED   = 'DISTRIBUTED';
    case CANCELED      = 'CANCELED';
}
