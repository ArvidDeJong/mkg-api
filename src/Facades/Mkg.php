<?php

namespace Darvis\Mkg\Facades;

use Illuminate\Support\Facades\Facade;

class Mkg extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mkg';
    }
}
