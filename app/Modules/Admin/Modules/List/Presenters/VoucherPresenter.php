<?php declare(strict_types = 1);

namespace App\Modules\Admin\Modules\List\Presenters;

use App\Forms\Form;
use App\Models\Repositories\StoreRepository;
use App\Models\Repositories\VoucherRepository;
use App\Models\Voucher;
use App\Modules\Admin\Presenters\BasePresenter;
use Nette\Application\AbortException;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use ReflectionException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

final class VoucherPresenter extends BasePresenter
{
    /**
     * @var Voucher|null
     */
    private ?Voucher $voucher = null;

    /**
     * @var VoucherRepository
     * @inject
     */
    public VoucherRepository $voucherRepository;

    /**
     * @var StoreRepository
     * @inject
     */
    public StoreRepository $storeRepository;

    /**
     * @return Form
     * @throws ReflectionException
     */
    protected function createComponentForm(): Form
    {
        $builder = $this->builderFactory
            ->getBuilder()
            ->addIgnored('createdOn')
            ->addRequired('ean')
            ->addRequired('title')
            ->addRequired('code');

        $form = $builder->create($this->voucher);

        $form->onSuccess[] = [$this, 'handleUpdate'];

        return $form;
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function actionEdit(int $id): void
    {
        if (! $this->voucher = $this->voucherRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }
    }

    /**
     * @param Form $form
     * @return never
     * @throws AbortException
     * @throws ReflectionException
     */
    public function handleUpdate(Form $form): never
    {
        $this->mapper->mapForm($this->voucher, $form);

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.Saved'));
        $this->redirect(':');
    }

    /**
     * @param string $name
     * @return DataGrid
     * @throws DataGridException
     */
    protected function createComponentListGrid(string $name): Datagrid
    {
        $grid = $this->createDatagrid($name);

        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('voucher')
            ->from(Voucher::class, 'voucher');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('title', $this->translator->translate('forms.title'))
            ->setRenderer(function (Voucher $voucher) {
                return $voucher->getTitle();
            })
            ->setEditableValueCallback(
                function (Voucher $voucher) {
                    return $voucher->getTitle();
                }
            )
            ->setFilterText();

        $grid->addColumnText('ean', $this->translator->translate('forms.ean'))
            ->setRenderer(function (Voucher $voucher) {
                return $voucher->getEan();
            })
            ->setEditableValueCallback(
                function (Voucher $voucher) {
                    return $voucher->getEan();
                }
            )
            ->setFilterText();

        $grid->addColumnText('code', $this->translator->translate('forms.code'))
            ->setRenderer(function (Voucher $voucher) {
                return $voucher->getEan();
            })
            ->setEditableValueCallback(
                function (Voucher $voucher) {
                    return $voucher->getEan();
                }
            )
            ->setFilterText();

        $grid->addColumnText('store', $this->translator->translate('forms.store'))
            ->setRenderer(function (Voucher $voucher) {
                return $voucher->getStore()->getTitle();
            })
            ->setEditableValueCallback(
                function (Voucher $voucher) {
                    return $voucher->getStore()->getId();
                }
            )
            ->setFilterText();

        $inlineEdit = $grid->addInlineEdit();

        $inlineEdit->onControlAdd[] = function (Container $container): void
        {
            $container->addText('title')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('ean')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('code')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addSelect('store', '', $this->storeRepository->getPairs())
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineEdit->onSetDefaults[] = static function (Container $container, Voucher $voucher): void {
            $container->setDefaults([
                'id' => $voucher->getId(),
                'title' => $voucher->getTitle(),
                'ean' => $voucher->getEan(),
                'code' => $voucher->getCode(),
                'store' => $voucher->getStore()->getId()
            ]);
        };

        $inlineEdit->onSubmit[] = function ($id, $values): void {
            if (! $voucher = $this->voucherRepository->find((int) $id)) {
                $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
                if ($this->isAjax()) {
                    $this->redrawControl('flashes');
                    $this->redrawControl('grid');
                    return;
                }

                $this->redirect(':');
            }

            if (! $store = $this->storeRepository->find((int) $values->offsetGet('store'))) {
                $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
                if ($this->isAjax()) {
                    $this->redrawControl('flashes');
                    $this->redrawControl('grid');
                    return;
                }

                $this->redirect(':');
            }

            $voucher->setEan($values->offsetGet('ean'))
                ->setTitle($values->offsetGet('title'))
                ->setCode($values->offsetGet('code'))
                ->setStore($store);
            $this->entityManager->flush();

            $this->flashMessage($this->translator->translate('messages.RowWasEdited'));

            if ($this->isAjax()) {
                $this->redrawControl('flashes');
                $this->redrawControl('grid');
            } else {
                $this->redirect(':');
            }
        };

        $inlineEdit->setShowNonEditingColumns();

        $inlineAdd = $grid->addInlineAdd();

        $inlineAdd->setPositionTop()
            ->onControlAdd[] = function (Container $container): void {
            $container->addText('title', '')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('ean', '')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('code')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addSelect('store', '', $this->storeRepository->getPairs())
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineAdd->onSubmit[] = function (ArrayHash $values): void {
            if ($this->voucherRepository->findOneBy(['code' => $values->offsetGet('code')])) {
                $this->flashMessage($this->translator->translate('errors.RowAlreadyExist'));
                $this->redrawControl('flashes');
            }

            if (! $store = $this->storeRepository->find((int) $values->offsetGet('store'))) {
                $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
                if ($this->isAjax()) {
                    $this->redrawControl('flashes');
                    $this->redrawControl('grid');
                    return;
                }

                $this->redirect(':');
            }

            $voucher = (new Voucher())
                ->setEan($values->offsetGet('ean'))
                ->setCode($values->offsetGet('code'))
                ->setStore($store);

            $this->entityManager->persist($voucher);
            $this->entityManager->flush();

            $this->flashMessage($this->translator->translate('messages.RowWasSaved'));

            if ($this->isAjax()) {
                $this->redrawControl('flashes');
                $this->redrawControl('grid');
            } else {
                $this->redirect(':');
            }
        };

        $grid->addAction('delete!', '', ':delete!', ['id' => 'id'])
            ->setIcon('trash')
            ->setTitle($this->translator->translate('actions.Delete'))
            ->setClass('btn btn-danger waves-effect waves-light ajax')
            ->setConfirmation(
                new StringConfirmation('Opravdu chcete odstranit?')
            );

        return $grid;
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function handleDelete(int $id): void
    {
        if (! $voucher = $this->voucherRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            if ($this->isAjax()) {
                $this->redrawControl('flashes');
                return;
            }

            $this->redirect(':');
        }

        $this->entityManager->flush();
        $this->flashMessage($this->translator->translate('messages.Deleted'));
        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            return;
        }

        $this->redirect(':');
    }
}
