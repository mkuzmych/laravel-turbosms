<?php

namespace Uapixart\LaravelTurbosms;

use Illuminate\Support\Facades\Facade;

/**
* Class TurbosmsFacade
*/
class TurbosmsFacade extends Facade
{
    /**
	* Get the registered name of the component.
	*
	* @return string
	*/
    protected static function getFacadeAccessor() { 
        return 'laravel-turbosms';
    }
}