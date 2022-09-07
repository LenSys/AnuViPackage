<?php

namespace App\AnuVi\Facades;

use App\AnuVi\PixabayManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\AnuVi\WordPressManager
 */
class Pixabay extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return \App\AnuVi\WordPressManager
	 */
	protected static function getFacadeAccessor()
	{
		return PixabayManager::class;
	}
}