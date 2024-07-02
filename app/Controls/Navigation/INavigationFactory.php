<?php declare(strict_types=1);

namespace App\Controls\Navigation;

interface INavigationFactory
{
    /**
     * @return Navigation
     */
	public function create(): Navigation;
}
