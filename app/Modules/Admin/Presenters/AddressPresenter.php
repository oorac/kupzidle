<?php

namespace App\Modules\Admin\Presenters;

use App\Controls\Address\Control\AddressControl;
use App\Controls\Address\Control\IAddressControlFactory;
use App\Controls\Address\Grid\IAddressGridFactory;
use App\Models\Address;
use App\Models\Repositories\AddressRepository;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;

final class AddressPresenter extends BasePresenter
{
    /**
     * @var IAddressControlFactory
     * @inject
     */
    public IAddressControlFactory $addressControlFactory;

    /**
     * @var IAddressGridFactory
     * @inject
     */
    public IAddressGridFactory $addressGridFactory;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @param string $name
     * @return DataGrid
     */
    protected function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->createDatagrid($name);

        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('address')
            ->from(Address::class, 'address');

        return $this->addressGridFactory->create($grid, $queryBuilder, $this->translator, $this);
    }

    /**
     * @return AddressControl
     */
    protected function createComponentForm(): AddressControl
    {
        $control = $this->addressControlFactory->create($this->translator, $this);

        $control->onAdd[] = function (AddressControl $control, Address $address): void {
            $this->presenter->flashMessage($this->translator->translate('messages.RowWasSaved') .' - ' . $address->getTitle());
            if ($this->presenter->isAjax()) {
                $this->redrawControl('flashes');
                $this->redrawControl('grid');
            }
        };

        $control->onEdit[] = function (AddressControl $control, Address $address): void {
            $this->presenter->flashMessage($this->translator->translate('messages.RowWasSaved') .' - ' . $address->getTitle());
            if ($this->presenter->isAjax()) {
                $this->redrawControl('flashes');
                $this->redrawControl('grid');
            }
        };

        $control->onEditError[] = function (AddressControl $control, string $messages): void {
            $this->presenter->flashMessage($messages, self::FM_ERROR);
            if ($this->presenter->isAjax()) {
                $this->redrawControl('flashes');
                $this->redrawControl('grid');
            }
        };

        $control->onAddError[] = function (AddressControl $control, string $messages): void {
            $this->presenter->flashMessage($messages, self::FM_ERROR);
            if ($this->presenter->isAjax()) {
                $this->redrawControl('flashes');
                $this->redrawControl('grid');
            }
        };

        return $control;
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function handleDelete(int $id): void
    {
        if (! $address = $this->addressRepository->find($id)) {
            $this->entityManager->remove($address);

            $this->flashMessage($this->translator->translate('messages.RowWasDeleted'));
        } else {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
        }

        $this->redirect(':');
    }
}
