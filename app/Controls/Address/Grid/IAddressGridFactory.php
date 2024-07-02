<?php declare(strict_types=1);

namespace App\Controls\Address\Grid;

use App\Translator\Translator;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

interface IAddressGridFactory
{
    /**
     * @param DataGrid $grid
     * @param QueryBuilder $queryBuilder
     * @param Translator $translator
     * @param Presenter $presenter
     * @return AddressGrid
     */
	public function create(DataGrid $grid, QueryBuilder $queryBuilder, Translator $translator, Presenter $presenter): AddressGrid;
}
