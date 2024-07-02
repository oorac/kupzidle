<?php declare(strict_types=1);

namespace App\Controls\Breadcrumbs;

use App\Utils\LazyObject;

interface IBreadcrumbsFactory
{
    /**
     * @param LazyObject $presenterInfo
     * @return Breadcrumbs
     */
	public function create(LazyObject $presenterInfo): Breadcrumbs;
}
