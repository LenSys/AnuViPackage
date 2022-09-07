<?php

namespace App\AnuVi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\AnuVi\GoogleManager
 */
class Google extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return \App\AnuVi\GoogleManager
	 */
	protected static function getFacadeAccessor()
	{
		return \App\AnuVi\GoogleManager::class;
	}
}