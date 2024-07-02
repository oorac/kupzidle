<?php declare(strict_types = 1);

namespace App\Modules\Admin\Modules\List\Presenters;

use App\Forms\Form;
use App\Models\Repositories\StoreRepository;
use App\Models\Store;
use App\Modules\Admin\Presenters\BasePresenter;
use Nette\Application\AbortException;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use ReflectionException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

final class StorePresenter extends BasePresenter
{
    /**
     * @var Store|null
     */
    private ?Store $store = null;

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
            ->addRequired('title')
            ->addRequired('code')
            ->addRequired('storeId');

        $form = $builder->create($this->store);

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
        if (! $this->store = $this->storeRepository->find($id)) {
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
        $this->mapper->mapForm($this->store, $form);

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
            ->select('store')
            ->from(Store::class, 'store');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('title', $this->translator->translate('forms.title'))
            ->setRenderer(function (Store $store) {
                return $store->getTitle();
            })
            ->setEditableValueCallback(
                function (Store $store) {
                    return $store->getTitle();
                }
            )
            ->setFilterText();

        $grid->addColumnText('code', $this->translator->translate('forms.code'))
            ->setRenderer(function (Store $store) {
                return $store->getCode();
            })
            ->setEditableValueCallback(
                function (Store $store) {
                    return $store->getCode();
                }
            )
            ->setFilterText();

        $grid->addColumnText('storeId', $this->translator->translate('forms.storeId'))
            ->setRenderer(function (Store $store) {
                return $store->getStoreId();
            })
            ->setEditableValueCallback(
                function (Store $store) {
                    return $store->getStoreId();
                }
            )
            ->setFilterText();

        $inlineEdit = $grid->addInlineEdit();

        $inlineEdit->onControlAdd[] = function (Container $container): void
        {
            $container->addText('title')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('code')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('storeId')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineEdit->onSetDefaults[] = static function (Container $container, Store $store): void {
            $container->setDefaults([
                'id' => $store->getId(),
                'title' => $store->getTitle(),
                'code' => $store->getCode(),
                'storeId' => $store->getStoreId()
            ]);
        };

        $inlineEdit->onSubmit[] = function ($id, $values): void {
            if (! $store = $this->storeRepository->find((int) $id)) {
                $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
                if ($this->isAjax()) {
                    $this->redrawControl('flashes');
                    $this->redrawControl('grid');
                    return;
                }

                $this->redirect(':');
            }

            $store->setTitle($values->offsetGet('title'))
                ->setCode($values->offsetGet('code'))
                ->setStoreId($values->offsetGet('storeId'));
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
            $container->addText('code')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addText('storeId')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineAdd->onSubmit[] = function (ArrayHash $values): void {
            if ($this->storeRepository->findOneBy(['storeId' => $values->offsetGet('storeId')])) {
                $this->flashMessage($this->translator->translate('errors.RowAlreadyExist'));
                $this->redrawControl('flashes');
            }

            $paymentMethod = (new Store())
                ->setTitle($values->offsetGet('title'))
                ->setCode($values->offsetGet('code'))
                ->setStoreId($values->offsetGet('storeId'));

            $this->entityManager->persist($paymentMethod);
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
        if (! $store = $this->storeRepository->find($id)) {
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
