<?php

namespace App\AnuVi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\AnuVi\AmazonManager
 */
class Amazon extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return \App\AnuVi\AmazonManager
	 */
	protected static function getFacadeAccessor()
	{
		return \App\AnuVi\AmazonManager::class;
	}
}