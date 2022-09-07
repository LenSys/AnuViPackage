<?php

namespace App\AnuVi\Facades;

use App\AnuVi\WordPressManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\AnuVi\WordPressManager
 */
class WordPress extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return \App\AnuVi\WordPressManager
	 */
	protected static function getFacadeAccessor()
	{
		return WordPressManager::class;
	}
}