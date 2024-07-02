<?php

namespace App\Modules\Admin\Presenters;

use App\Forms\Form;
use App\Models\Product;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\DocumentRepository;
use App\Models\Repositories\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

final class DocumentPresenter extends BasePresenter
{
    /**
     * @var DocumentRepository
     * @inject
     */
    public DocumentRepository $documentRepository;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @param string $name
     * @return DataGrid
     * @throws DataGridException
     */
    protected function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->createDatagrid($name);

        $queryBuilder = $this->documentRepository->createQueryBuilder('d');

        $grid->setDataSource($queryBuilder);
        $grid->setDefaultSort('createdOn', 'ASC');

        $grid->addColumnText('title', $this->translator->translate('forms.title'))
            ->setRenderer(function (Product $product) {
                return $product->getTitle();
            })
            ->setSortable()
            ->setSortableCallback(function (QueryBuilder $qb, $sort) {
                if ($sort['title']) {
                    $qb->orderBy('p.title', $sort['title']);
                }
            })
            ->setFilterText()
            ->setCondition(function (QueryBuilder $qb, $value) {
                $qb->andWhere('LOWER(p.title) LIKE :title')
                    ->setParameter('title', '%' . strtolower($value) . '%');
            });

        $grid->addColumnText('supplier', $this->translator->translate('forms.supplier'))
            ->setRenderer(function (Product $product) {
                return $product->getSupplier()?->getInvoiceAddress()->getTitle();
            })
            ->setSortable()
            ->setSortableCallback(function (QueryBuilder $qb, $sort) {
                if ($sort['supplier']) {
                    $qb->leftJoin('p.supplier', 'addressSort')
                        ->orderBy('address.title', $sort['supplier']);
                }
            })
            ->setFilterSelect(['' => $this->translator->translate('texts.-all-')] + $this->addressRepository->getPairsSupplier())
            ->setCondition(function (QueryBuilder $qb, $value) {
                $qb->leftJoin('p.supplier', 'address')
                    ->andWhere('LOWER(address.title) LIKE :supplier')
                    ->setParameter('supplier', '%' . strtolower($value) . '%');
            });

        $grid->addColumnText('ean', $this->translator->translate('forms.ean'))
            ->setRenderer(function (Product $product) {
                return $product->getEan();
            });

        $grid->addColumnText('productCode', $this->translator->translate('forms.productCode'))
            ->setRenderer(function (Product $product) {
                return $product->getProductCode();
            });

        $grid->addColumnText('supplierCode', $this->translator->translate('forms.supplierCode'))
            ->setRenderer(function (Product $product) {
                return $product->getSupplierCode();
            });

        $grid->addGroupAction($this->translator->translate('actions.ChangeStatus'), [
            Product::STATUS_CREATED => $this->translator->translate('texts.productStatus.' . Product::STATUS_CREATED),
            Product::STATUS_SYNC => $this->translator->translate('texts.productStatus.' . Product::STATUS_SYNC),
            Product::STATUS_DELETED => $this->translator->translate('texts.productStatus.' . Product::STATUS_DELETED),
        ])->onSelect[] = [$this, 'changeStatus'];

        $grid->addAction('edit', '', 'edit')
            ->setTitle($this->translator->translate('actions.Edit'))
            ->setIcon('edit')
            ->setClass('btn btn-primary');

        $grid->addAction('delete', '', 'delete!')
            ->setTitle($this->translator->translate('actions.Delete'))
            ->setIcon('trash')
            ->setClass('btn btn-danger ajax');

        return $grid;
    }
    
}
