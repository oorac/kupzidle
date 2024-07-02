<?php

namespace App\Modules\Admin\Modules\Settings\Presenters;

use App\Forms\Form;
use App\Models\Feed;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\CustomerRepository;
use App\Models\Repositories\FeedRepository;
use App\Modules\Admin\Presenters\BasePresenter;
use App\Services\XmlParser;
use GuzzleHttp\Exception\GuzzleException;
use Nette\Application\AbortException;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

final class FeedPresenter extends BasePresenter
{
    /**
     * @var FeedRepository
     * @inject
     */
    public FeedRepository $feedRepository;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var XmlParser
     * @inject
     */
    public XmlParser $xmlParser;

    /**
     * @var CustomerRepository
     * @inject
     */
    public CustomerRepository $customerRepository;

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->addHidden('id');

        $form->addSelect('supplier', $this->translator->translate('forms.supplier'), ['' => $this->translator->translate('texts.-choose-')] + $this->addressRepository->getPairsSupplier());

        $form->addText('url', $this->translator->translate('forms.url'))
            ->setRequired();

        $listType = [
            Feed::TYPE_IMPORT => $this->translator->translate('texts.typeFeed.' . Feed::TYPE_IMPORT),
            Feed::TYPE_EXPORT => $this->translator->translate('texts.typeFeed.' . Feed::TYPE_EXPORT),
        ];

        $listSubType = [
            Feed::SUBTYPE_PRODUCT => $this->translator->translate('texts.subTypeFeed.' . Feed::SUBTYPE_PRODUCT),
            Feed::SUBTYPE_ORDER => $this->translator->translate('texts.subTypeFeed.' . Feed::SUBTYPE_ORDER)
        ];

        $form->addSelect('type', $this->translator->translate('forms.type'), $listType);
        $form->addSelect('subType', $this->translator->translate('forms.subType'), $listSubType);

        $form->addText('xslFileName', $this->translator->translate('forms.xslFileName'));
        $form->addText('outputName', $this->translator->translate('forms.outputName'));
        $form->addText('username', $this->translator->translate('forms.username'));
        $form->addText('password', $this->translator->translate('forms.password'));

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

        $supplier = null;
        if (! empty($data->offsetGet('supplier')) && ! $supplier = $this->customerRepository->find((int)$data->offsetGet('supplier'))) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        if (empty ($data->offsetGet('id')) && ! $this->feedRepository->findOneBy(['supplier' => $supplier])) {
            $feed = new Feed();
            $this->entityManager->persist($feed);
        } elseif (! $feed = $this->feedRepository->find((int) $data->offsetGet('id'))) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        $feed->setUrl($data->offsetGet('url'))
            ->setXslFileName($data->offsetGet('xslFileName'))
            ->setType($data->offsetGet('type'))
            ->setOutputName($data->offsetGet('outputName'))
            ->setSubType($data->offsetGet('subType'))
            ->setPassword($data->offsetGet('password'))
            ->setUsername($data->offsetGet('username'))
            ->setSupplier($supplier);

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
            ->select('feed')
            ->from(Feed::class, 'feed');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('supplier', $this->translator->translate('forms.supplier'))
            ->setRenderer(function (Feed $feed) {
                return $feed->getSupplier()?->getInvoiceAddress()->getTitle() ?? '';
            })
            ->setFilterText();

        $grid->addColumnText('xslFileName', $this->translator->translate('forms.xslFileName'))
            ->setRenderer(function (Feed $feed) {
                return $feed->getXslFileName();
            })
            ->setFilterText();

        $grid->addColumnText('url', $this->translator->translate('forms.url'))
            ->setRenderer(function (Feed $feed) {
                return $feed->getUrl();
            })
            ->setFilterText();

        $listType = [
            Feed::TYPE_IMPORT => $this->translator->translate('texts.typeFeed.' . Feed::TYPE_IMPORT),
            Feed::TYPE_EXPORT => $this->translator->translate('texts.typeFeed.' . Feed::TYPE_EXPORT),
        ];

        $listSubType = [
            Feed::SUBTYPE_PRODUCT => $this->translator->translate('texts.subTypeFeed.' . Feed::SUBTYPE_PRODUCT),
            Feed::SUBTYPE_ORDER => $this->translator->translate('texts.subTypeFeed.' . Feed::SUBTYPE_ORDER)
        ];

        $grid->addColumnText('type', $this->translator->translate('forms.type'))
            ->setRenderer(function (Feed $feed) {
                return $this->translator->translate('texts.typeFeed.' . $feed->getType());
            })
            ->setFilterSelect(['' => $this->translator->translate('texts.-all-')] + $listType);

        $grid->addColumnText('subType', $this->translator->translate('forms.subType'))
            ->setRenderer(function (Feed $feed) {
                return $this->translator->translate('texts.subTypeFeed.' . $feed->getSubType());
            })
            ->setFilterSelect(['' => $this->translator->translate('texts.-all-')] + $listSubType);

        $grid->addAction(':edit', $this->translator->translate('actions.Edit'));
        $grid->addAction(':download!', '', ':download!')
            ->setIcon('download')
            ->setTitle($this->translator->translate('actions.Download'))
            ->setClass('btn btn-success');

        return $grid;
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     */
    public function actionEdit(int $id): void
    {
        if (! $feed = $this->feedRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        $this->getComponent('form')?->setDefaults([
            'id' => $feed->getId(),
            'supplier' => $feed->getSupplier()?->getId(),
            'url' => $feed->getUrl(),
            'type' => $feed->getType(),
            'subType' => $feed->getSubType(),
            'outputName' => $feed->getOutputName(),
            'xslFileName' => $feed->getXslFileName(),
            'username' => $feed->getUsername(),
            'password' => $feed->getPassword()
        ]);
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws GuzzleException
     */
    public function handleDownload(int $id): void
    {
        if (! $feed = $this->feedRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        if ($this->xmlParser->processDownload($feed)) {
            $this->flashMessage($this->translator->translate('messages.DownloadedSuccess'));
        } else {
            $this->flashMessage($this->translator->translate('errors.DownloadedFailed'), self::FM_ERROR);
        }

        $this->redirect(':');
    }
}
