<?php

namespace App\AnuVi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\AnuVi\WebDataManager
 */
class WebData extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return \App\AnuVi\WebDataManager
	 */
	protected static function getFacadeAccessor()
	{
		return \App\AnuVi\WebDataManager::class;
	}
}