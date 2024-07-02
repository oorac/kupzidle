<?php

namespace App\Modules\Admin\Presenters;

use App\Forms\Form;
use App\Models\Product;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

final class ProductPresenter extends BasePresenter
{
    /**
     * @var ProductRepository
     * @inject
     */
    public ProductRepository $productRepository;

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

        $queryBuilder = $this->productRepository->createQueryBuilder('p');

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
                return $product->getSupplier()->getTitle();
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

    /**
     * @param int $id
     * @return void
     */
    public function handleDelete(int $id): void
    {
        if (! $product = $this->productRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redrawControl('flashes');
            $this->redrawControl('grid');
        }

        $product->setStatus(Product::STATUS_DELETED);

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.Deleted'));
        $this->redrawControl('flashes');
        $this->redrawControl('grid');
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->addHidden('id');

        $form->addText('title', $this->translator->translate('forms.title'))
            ->setRequired();

        $form->addText('productCode', $this->translator->translate('forms.productCode'))
            ->setRequired();

        $form->addText('plu', $this->translator->translate('forms.plu'))
            ->setRequired();

        $form->addText('articleId', $this->translator->translate('forms.articleId'))
            ->setRequired();

        $form->addText('supplierCode', $this->translator->translate('forms.supplierCode'))
            ->setRequired();

        $form->addSelect('supplier', $this->translator->translate('forms.supplier'), $this->addressRepository->getPairsSupplier());

        $form->addText('weight', $this->translator->translate('forms.weight'))
            ->setRequired();

        $form->addText('length', $this->translator->translate('forms.length'))
            ->setRequired();

        $form->addText('width', $this->translator->translate('forms.width'))
            ->setRequired();

        $form->addText('height', $this->translator->translate('forms.height'))
            ->setRequired();

        $form->addSubmit('submit', $this->translator->translate('actions.Save'));

        $form->onSuccess[] = [$this, 'processSave'];

        return $form;
    }

    /**
     * @param Form $form
     * @return never
     * @throws AbortException
     */
    public function processSave(Form $form): never
    {
        $data = $form->getValues();

        if (! $product = $this->productRepository->find((int) $data->offsetGet('id'))) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.Saved'));
        $this->redirect(':');
    }

}
