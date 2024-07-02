<?php declare(strict_types = 1);

namespace App\Modules\Admin\Modules\List\Presenters;

use App\Forms\Form;
use App\Models\DeliveryMethod;
use App\Models\Repositories\DeliveryMethodRepository;
use App\Models\Repositories\StoreRepository;
use App\Modules\Admin\Presenters\BasePresenter;
use Nette\Application\AbortException;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use ReflectionException;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

final class DeliveryMethodPresenter extends BasePresenter
{
    /**
     * @var DeliveryMethod|null
     */
    private ?DeliveryMethod $deliveryMethod = null;

    /**
     * @var DeliveryMethodRepository
     * @inject
     */
    public DeliveryMethodRepository $deliveryMethodRepository;

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
            ->addRequired('code');

        $form = $builder->create($this->deliveryMethod);

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
        if (! $this->deliveryMethod = $this->deliveryMethodRepository->find($id)) {
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
        $this->mapper->mapForm($this->deliveryMethod, $form);

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
            ->select('deliveryMethod')
            ->from(DeliveryMethod::class, 'deliveryMethod');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('title', $this->translator->translate('forms.title'))
            ->setRenderer(function (DeliveryMethod $deliveryMethod) {
                return $deliveryMethod->getTitle();
            })
            ->setEditableValueCallback(
                function (DeliveryMethod $deliveryMethod) {
                    return $deliveryMethod->getTitle();
                }
            )
            ->setFilterText();

        $grid->addColumnText('code', $this->translator->translate('forms.code'))
            ->setRenderer(function (DeliveryMethod $deliveryMethod) {
                return $deliveryMethod->getCode();
            })
            ->setEditableValueCallback(
                function (DeliveryMethod $deliveryMethod) {
                    return $deliveryMethod->getCode();
                }
            )
            ->setFilterText();

        $grid->addColumnText('store', $this->translator->translate('forms.store'))
            ->setRenderer(function (DeliveryMethod $deliveryMethod) {
                return $deliveryMethod->getStore()->getTitle();
            })
            ->setEditableValueCallback(
                function (DeliveryMethod $deliveryMethod) {
                    return $deliveryMethod->getStore()->getId();
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
            $container->addSelect('store', '', $this->storeRepository->getPairs())
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineEdit->onSetDefaults[] = static function (Container $container, DeliveryMethod $deliveryMethod): void {
            $container->setDefaults([
                'id' => $deliveryMethod->getId(),
                'title' => $deliveryMethod->getTitle(),
                'code' => $deliveryMethod->getCode(),
                'store' => $deliveryMethod->getStore()->getId()
            ]);
        };

        $inlineEdit->onSubmit[] = function ($id, $values): void {
            if (! $deliveryMethod = $this->deliveryMethodRepository->find((int) $id)) {
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

            $deliveryMethod->setTitle($values->offsetGet('title'))
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
            $container->addText('code')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
            $container->addSelect('store', '', $this->storeRepository->getPairs())
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineAdd->onSubmit[] = function (ArrayHash $values): void {
            if ($this->deliveryMethodRepository->findOneBy(['title' => $values->offsetGet('title')])) {
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

            $deliveryMethod = (new DeliveryMethod())
                ->setTitle($values->offsetGet('title'))
                ->setCode($values->offsetGet('code'))
                ->setStore($store);

            $this->entityManager->persist($deliveryMethod);
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
        if (! $deliveryMethod = $this->deliveryMethodRepository->find($id)) {
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
