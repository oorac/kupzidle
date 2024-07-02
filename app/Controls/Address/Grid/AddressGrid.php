<?php declare(strict_types = 1);

namespace App\Controls\Address\Grid;

use App\Models\Address;
use App\Services\Doctrine\EntityManager;
use App\Translator\Translator;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

class AddressGrid extends DataGrid
{
    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    /**
     * @var Translator
     * @inject
     */
    public $translator;

    /**
     * @var Presenter
     */
    public Presenter $presenter;

    /**
     * @param DataGrid $grid
     * @param QueryBuilder $queryBuilder
     * @param Translator $translator
     * @param Presenter $presenter
     * @param EntityManager $entityManager
     * @throws DataGridException
     */
    public function __construct(
        Datagrid           $grid,
        QueryBuilder       $queryBuilder,
        Translator         $translator,
        Presenter          $presenter,
        EntityManager      $entityManager
    )
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->presenter = $presenter;

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('title', $this->translator->translate('forms.title'))
            ->setRenderer(function (Address $address) {
                return $address->getTitle();
            })
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('city', $this->translator->translate('forms.city'))
            ->setRenderer(function (Address $address) {
                return $address->getCity();
            })
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('zipCode', $this->translator->translate('forms.zipCode'))
            ->setRenderer(function (Address $address) {
                return $address->getZipCode();
            })
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('street', $this->translator->translate('forms.street'))
            ->setRenderer(function (Address $address) {
                return $address->getStreet();
            })
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('phone', $this->translator->translate('forms.phone'))
            ->setRenderer(function (Address $address) {
                return $address->getPhone();
            })
            ->setSortable()
            ->setFilterText();

        $grid->addAction('edit', '', ':edit', ['id' => 'id'])
            ->setIcon('edit')
            ->setTitle($this->translator->translate('actions.Edit'))
            ->setClass('btn btn-success waves-effect waves-light');

        $grid->addAction('delete!', '', ':delete!', ['id' => 'id'])
            ->setIcon('trash')
            ->setTitle($this->translator->translate('actions.Delete'))
            ->setClass('btn btn-danger waves-effect waves-light ajax')
            ->setConfirmation(
                new StringConfirmation('Opravdu chcete odstranit?')
            );
    }
}