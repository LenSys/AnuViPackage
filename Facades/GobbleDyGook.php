<?php

namespace App\AnuVi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\AnuVi\GobbleDyGookManager
 */
class GobbleDyGook extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return \App\AnuVi\GobbleDyGookManager
	 */
	protected static function getFacadeAccessor()
	{
		return \App\AnuVi\GobbleDyGookManager::class;
	}
}