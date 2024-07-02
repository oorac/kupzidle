<?php

namespace App\Modules\Admin\Presenters;

use App\Forms\Form;
use App\Models\Address;
use App\Models\Package;
use App\Models\Predict;
use App\Models\Repositories\AddressRepository;
use App\Models\Repositories\PackageRepository;
use App\Models\Repositories\PredictRepository;
use App\Models\Repositories\ServiceRepository;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\DpdService;
use App\Services\PackageService;
use App\Utils\FileSystem;
use App\Utils\Number;
use DateTime;
use DOMDocument;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Container;
use Nette\Http\FileUpload;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Exception\DataGridException;

final class DashboardPresenter extends BasePresenter
{
    /**
     * @var DpdService
     * @inject
     */
    public DpdService $dpdService;

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var PredictRepository
     * @inject
     */
    public PredictRepository $predictRepository;

    /**
     * @var PackageRepository
     * @inject
     */
    public PackageRepository $packageRepository;

    /**
     * @var ServiceRepository
     * @inject
     */
    public ServiceRepository $serviceRepository;

    /**
     * @var PackageService
     * @inject
     */
    public PackageService $packageService;

    /**
     * @var null|Package
     */
    private ?Package $package = null;

    /**
     * @param string $name
     * @return DataGrid
     * @throws DataGridException
     * @throws DataGridColumnStatusException
     */
    protected function createComponentListPackage(string $name): DataGrid
    {
        $grid = $this->createDatagrid($name);

        $queryBuilder = $this->packageRepository->createQueryBuilder('p');

        $grid->setDataSource($queryBuilder);
        $grid->setDefaultSort('createdOn', 'ASC');

        $grid->addColumnText('id', '#')
            ->setRenderer(function (Package $package) {
                return $package->getId();
            });

        $grid->addColumnText('sender', $this->translator->translate('forms.sender'))
            ->setRenderer(function (Package $package) {
                return $package->getSender()->getTitle();
            });

        $status = $grid->addColumnStatus('status', $this->translator->translate('forms.status'))
            ->setRenderer(function (Package $package) {
                return $this->translator->translate('texts.packageStatus.' . $package->getStatus());
            })
            ->setRenderCondition(function (Package $package) {
                return !($package->getStatus() === Package::STATUS_SEND
                    && $package->getTransaction() !== null
                    && (
                        $package->getTransaction()->getCreatedOn() < (new DateTime())
                        || (
                            $package->getTransaction()->getCreatedOn() == (new DateTime())
                            && (new DateTime()) > (new DateTime('18:00:00'))
                        )
                    ));
            })
            ->addOption(Package::STATUS_CREATED, $this->translator->translate('texts.packageStatus.' . Package::STATUS_CREATED))
            ->setClass('btn-success')
            ->endOption()
            ->addOption(Package::STATUS_SEND, $this->translator->translate('texts.packageStatus.' . Package::STATUS_SEND))
            ->setClass('btn-warning')
            ->setClass('hidden')
            ->endOption()
            ->addOption(Package::STATUS_DELETED, $this->translator->translate('texts.packageStatus.' . Package::STATUS_DELETED))
            ->setClass('btn-warning')
            ->setClass('hidden')
            ->endOption();
        $status->onChange[] = [$this, 'changeStatus'];

        $grid->addColumnText('countryCode', $this->translator->translate('forms.countryCode'))
            ->setRenderer(function (Package $package) {
                return $package->getSender()->getCountryCode();
            });

        $grid->addColumnText('receiver', $this->translator->translate('forms.receiver'))
            ->setRenderer(function (Package $package) {
                return $package->getReceiver()->getTitle();
            });

        $grid->addColumnText('codAmount', $this->translator->translate('forms.codAmount'))
            ->setRenderer(function (Package $package) {
                return Number::getPriceWithCurrency(
                    $package->getService()->getCodAmount(),
                    $this->translator->translate('texts.codCurrency.' . $package->getService()->getCodCurrency())
                );
            });

        $grid->addColumnText('codCurrency', $this->translator->translate('forms.codCurrency'))
            ->setRenderer(function (Package $package) {
                return $this->translator->translate('texts.codCurrency.' . $package->getService()->getCodCurrency());
            });

        $grid->addColumnText('countParcel', $this->translator->translate('forms.countParcel'))
            ->setRenderer(function (Package $package) {
                return $package->getCountParcel();
            });

        $grid->addColumnText('ref1', $this->translator->translate('forms.ref1'))
            ->setRenderer(function (Package $package) {
                return $package->getService()->getRef1();
            });

        $grid->addColumnText('ref2', $this->translator->translate('forms.ref2'))
            ->setRenderer(function (Package $package) {
                return $package->getService()->getRef2();
            });

        $grid->addAction('send', '', 'send!')
            ->setTitle($this->translator->translate('actions.Send'))
            ->setIcon('paper-plane')
            ->setClass('btn btn-success');

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
     * @param string $id
     * @param string $status
     * @return never
     * @throws AbortException
     */
    public function changeStatus(string $id, string $status): never
    {
        if (! $package = $this->packageRepository->find((int) $id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect(':');
        }

       if (
           $status !== Package::STATUS_SEND
           && $package->getStatus() === Package::STATUS_SEND
           && $package->getTransaction() !== null
       ) {
           $this->dpdService->deleteDomesticCollection([$package->getTransaction()->getCollectionRequestId()]);
           $package->setStatus(Package::STATUS_DELETED);
           $this->flashMessage($this->translator->translate('messages.DeleteDomesticCollection'));
       } elseif ($package->getStatus() !== Package::STATUS_SEND) {
           $package->setStatus($status);
           $this->flashMessage($this->translator->translate('messages.Changed'));
       } elseif ($status === Package::STATUS_SEND) {
           $dataTransform = $this->packageService->prepareDataForApi([$package]);
           [$flashMessage, $listTransaction] = $this->packageService->processResponse($this->dpdService->postDomesticCollection($dataTransform), $dataTransform);

           /** @var Transaction $transaction */
           foreach ($listTransaction as $transaction) {
               $package->setTransaction($transaction);
           }

           $this->flashMessage($flashMessage['message'], $flashMessage['type']);
       } else {
           $package->setStatus($status);
           $this->flashMessage($this->translator->translate('messages.Changed'));
       }

       $this->entityManager->flush();

       $this->redirect('this');
    }

    /**
     * @param int $id
     * @return void
     */
    public function handleSend(int $id): void
    {
        if (! $package = $this->packageRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redrawControl('flashes');
            $this->redrawControl('grid');
        }

        $dataTransform = $this->packageService->prepareDataForApi([$package]);
        [$flashMessage, $listTransaction] = $this->packageService->processResponse($this->dpdService->postDomesticCollection($dataTransform), $dataTransform);

        /** @var Transaction $transaction */
        foreach ($listTransaction as $transaction) {
            $package->setTransaction($transaction);
        }

        $this->entityManager->flush();

        $this->flashMessage($flashMessage['message'], $flashMessage['type']);
        $this->redrawControl('flashes');
        $this->redrawControl('grid');
    }

    /**
     * @param int $id
     * @return void
     */
    public function handleDelete(int $id): void
    {
        if (! $package = $this->packageRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redrawControl('flashes');
            $this->redrawControl('grid');
        }

        if ($package->getTransaction() !== null) {
            $this->dpdService->deleteDomesticCollection([$package->getTransaction()->getCollectionRequestId()]);
        }

        $package->setStatus(Package::STATUS_DELETED);

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.DeleteDomesticCollection'));
        $this->redrawControl('flashes');
        $this->redrawControl('grid');
    }

    /**
     * @return Form
     * @throws InvalidLinkException
     */
    protected function createComponentLoadXML(): Form
    {
        $form = new Form();

        $form->addUpload('xml', $this->translator->translate('forms.xml'))
            ->addRule($form::MIME_TYPE, 'Soubor musí být ve formátu XML', 'text/xml')
            ->setRequired();

        $listCountryCode = [
            Address::COUNTRY_CODE_CZ => $this->translator->translate('texts.countryCode.' . Address::COUNTRY_CODE_CZ),
            Address::COUNTRY_CODE_SK => $this->translator->translate('texts.countryCode.' . Address::COUNTRY_CODE_SK),
        ];

        $countryCode = $form->addSelect('countryCode', $this->translator->translate('forms.countryCode'), ['' => 'Vyberte ze seznamu'] + $listCountryCode)
            ->setRequired();

        $sender = $form->addSelect('sender', $this->translator->translate('forms.sender'))
            ->setRequired();

        $countryCode->setHtmlAttribute('data-url', $this->link(':Api:App:addressByCountryCode', '#'))
            ->setHtmlAttribute('data-dependent', $sender->getHtmlName());

        $form->onAnchor[] = fn() =>
        $sender->setItems($countryCode->getValue() ? $this->addressRepository->getByDepoAndCountryCode($countryCode->getValue()) : ['' => 'Vyberte ze seznamu'] + $this->addressRepository->getByDepoAndCountryCode());

        $form->addSubmit('submit', $this->translator->translate('actions.Send'));

        $form->onSuccess[] = [$this, 'processUpload'];

        return $form;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    public function processUpload(Form $form): void
    {
        $data = $form->getValues();

        $xmlFile = $data->xml;
        /**
         * @var FileUpload $xmlFile
         */
        if ($xmlFile->isOk()) {
            $xmlContent = FileSystem::read($xmlFile->getTemporaryFile());
            $dom = new DOMDocument();
            if (@$dom->loadXML($xmlContent)) {
                $rootTagName = $dom->documentElement->tagName;
                if ($rootTagName === 'S5Data') {
                    $sender = $this->addressRepository->findOneBy([
                        'id' => (int) $data->offsetGet('sender'),
                        'depo' => true,
                        'countryCode' => $data->offsetGet('countryCode')
                    ]);
                    $this->packageService->processCreateEntities($dom, $sender);
                    $this->flashMessage('Nahrávání XML souboru proběhlo úspěšně.');
                    $this->redirect(':');
                } else {
                    $this->flashMessage('XML soubor neobsahuje počáteční element <S5Data>.', self::FM_ERROR);
                }
            } else {
                $this->flashMessage('Chyba při načítání XML souboru.', self::FM_ERROR);
            }
        } else {
            $this->flashMessage('Nahrání souboru se nezdařilo.', self::FM_ERROR);
        }
    }

    /**
     * @param int $id
     * @return void
     */
    public function actionEdit(int $id): void
    {
        if (! $this->package = $this->packageRepository->find($id)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redrawControl('flashes');
            $this->redrawControl('grid');
        }

        $form = $this->getComponent('form');
        $form->setDefaults([
            'sender' => $this->package->getSender()->getId(),
            'city' => $this->package->getReceiver()->getCity(),
            'name' => $this->package->getReceiver()->getTitle(),
            'companyName' => $this->package->getReceiver()->getCompanyName(),
            'contactName' => $this->package->getReceiver()->getContactName(),
            'contactEmail' => $this->package->getReceiver()->getEmail(),
            'contactMobile' => $this->package->getReceiver()->getPhone(),
            'countryCode' => $this->package->getReceiver()->getCountryCode(),
            'street' => $this->package->getReceiver()->getStreet(),
            'zipCode' => $this->package->getReceiver()->getZipCode(),
            'codAmount' => $this->package->getService()->getCodAmount(),
            'codCurrency' => $this->package->getService()->getCodCurrency(),
            'parcelWeight' => $this->package->getService()->getParcelWeight(),
            'mainServiceElementCodes' => $this->package->getService()->getMainServiceElementCodes(),
            'ref1' => $this->package->getService()->getRef1(),
            'ref2' => $this->package->getService()->getRef2(),
            'pickupDate' => $this->package->getService()->getPickupDate()->format('d.m.Y'),
            'countParcel' => $this->package->getCountParcel()
        ]);
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = new Form();

        $form->addHidden('id');

        $listCountryCode = [
            Address::COUNTRY_CODE_CZ => $this->translator->translate('texts.countryCode.' . Address::COUNTRY_CODE_CZ),
            Address::COUNTRY_CODE_SK => $this->translator->translate('texts.countryCode.' . Address::COUNTRY_CODE_SK),
        ];

        $countryCode = $form->addSelect('countryCode', $this->translator->translate('forms.countryCode'), ['' => 'Vyberte ze seznamu'] + $listCountryCode)
            ->setRequired();

        $sender = $form->addSelect('sender', $this->translator->translate('forms.sender'))
            ->setRequired();

        $countryCode->setHtmlAttribute('data-url', $this->link(':Api:App:addressByCountryCode', '#'))
            ->setHtmlAttribute('data-dependent', $sender->getHtmlName());

        $form->onAnchor[] = fn() =>
        $sender->setItems($countryCode->getValue() ? $this->addressRepository->getByDepoAndCountryCode($countryCode->getValue()) : $this->addressRepository->getByDepoAndCountryCode($this->package->getSender()->getCountryCode()));

        $form->addText('name', $this->translator->translate('forms.name'))
            ->setRequired();

        $form->addText('companyName', $this->translator->translate('forms.companyName'))
            ->setRequired();

        $form->addText('contactName', $this->translator->translate('forms.contactName'))
            ->setRequired();

        $form->addText('contactEmail', $this->translator->translate('forms.contactEmail'))
            ->setRequired();

        $form->addText('contactMobile', $this->translator->translate('forms.contactMobile'))
            ->setRequired();

        $form->addText('city', $this->translator->translate('forms.city'))
            ->setRequired();

        $form->addText('street', $this->translator->translate('forms.street'))
            ->setRequired();

        $form->addInteger('zipCode', $this->translator->translate('forms.zipCode'))
            ->setRequired();

        $form->addText('codAmount', $this->translator->translate('forms.codAmount'))
            ->setDefaultValue(0);

        $listCodCurrency = [
            Service::COD_CURRENCY_CZK => $this->translator->translate('texts.codCurrency.' . Service::COD_CURRENCY_CZK),
            Service::COD_CURRENCY_EUR => $this->translator->translate('texts.codCurrency.' . Service::COD_CURRENCY_EUR),
        ];

        $form->addSelect('codCurrency', $this->translator->translate('forms.codCurrency'), $listCodCurrency);

        $form->addInteger('countParcel', $this->translator->translate('forms.countParcel'))
            ->setDefaultValue(1)
            ->setRequired();

        $form->addText('parcelWeight', $this->translator->translate('forms.parcelWeight'))
            ->setRequired();

        $form->addText('mainServiceElementCodes', $this->translator->translate('forms.mainServiceElementCodes'))
            ->setRequired();

        $form->addText('ref1', $this->translator->translate('forms.ref1'))
            ->setMaxLength(35);

        $form->addText('ref2', $this->translator->translate('forms.ref2'))
            ->setMaxLength(35);

        $form->addText('pickupDate', $this->translator->translate('forms.pickupDate'))
            ->setDefaultValue((new DateTime())->format('d.m.Y'))
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

        if (! $sender = $this->addressRepository->find((int) $data->offsetGet('sender'))) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'), self::FM_ERROR);
            $this->redirect('this');
        }

        if (! $receiver = $this->addressRepository->findOneBy([
            'city' => $data->offsetGet('city'),
            'title' => $data->offsetGet('name'),
            'companyName' => $data->offsetGet('companyName'),
            'contactName' => $data->offsetGet('contactName'),
            'email' => $data->offsetGet('contactEmail'),
            'phone' => $data->offsetGet('contactMobile'),
            'countryCode' => $data->offsetGet('countryCode'),
            'street' => $data->offsetGet('street'),
            'zipCode' => $data->offsetGet('zipCode')
        ])) {
            $receiver = (new Address())
                ->setCity($data->offsetGet('city'))
                ->setTitle($data->offsetGet('name'))
                ->setCompanyName($data->offsetGet('companyName'))
                ->setContactName($data->offsetGet('contactName'))
                ->setEmail($data->offsetGet('contactEmail'))
                ->setPhone($data->offsetGet('contactMobile'))
                ->setCountryCode($data->offsetGet('countryCode'))
                ->setStreet($data->offsetGet('street'))
                ->setZipCode($data->offsetGet('zipCode'));

            $this->entityManager->persist($receiver);
        }

        $this->package->setSender($sender)
            ->setReceiver($receiver)
            ->getService()->setCodAmount($data->offsetGet('codAmount'))
            ->setCodCurrency($data->offsetGet('codCurrency'))
            ->setParcelWeight($data->offsetGet('parcelWeight'))
            ->setMainServiceElementCodes($data->offsetGet('mainServiceElementCodes'))
            ->setRef1($data->offsetGet('ref1'))
            ->setRef2($data->offsetGet('ref2'))
            ->setPickupDate(new DateTime($data->offsetGet('pickupDate')));

        $this->package->setCountParcel((int) $data->offsetGet('countParcel'));

        $this->entityManager->flush();

        $this->flashMessage($this->translator->translate('messages.Saved'));
        $this->redirect(':');
    }

    /**
     * @param string $name
     * @return DataGrid
     * @throws DataGridException
     */
    protected function createComponentPredict(string $name): DataGrid
    {
        $grid = $this->createDatagrid($name);

        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('predict')
            ->from(Predict::class, 'predict');

        $grid->setDataSource(new DoctrineDataSource($queryBuilder, 'id'));

        $grid->addColumnText('destination', $this->translator->translate('forms.destination'))
            ->setEditableValueCallback(
                function (Predict $predict) {
                    return $predict->getDestination();
                }
            );

        $grid->addColumnText('type', $this->translator->translate('forms.type'))
            ->setRenderer(function (Predict $predict) {
                return $this->translator->translate('texts.predictType.' . $predict->getType());
            })
            ->setEditableValueCallback(
                function (Predict $predict) {
                    return $predict->getType();
                }
            );

        $inlineEdit = $grid->addInlineEdit();

        $inlineEdit->onControlAdd[] = function (Container $container): void
        {
            $container->addText('destination')
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));

            $listType = [
                Predict::TYPE_EMAIL => $this->translator->translate('texts.predictType.' . Predict::TYPE_EMAIL),
                Predict::TYPE_SMS => $this->translator->translate('texts.predictType.' . Predict::TYPE_SMS),
            ];
            $container->addSelect('type', '', $listType)
                ->setRequired($this->translator->translate('messages.ThisIsRequired'));
        };

        $inlineEdit->onSetDefaults[] = static function (Container $container, Predict $predict): void {
            $container->setDefaults([
                'id' => $predict->getId(),
                'destination' => $predict->getDestination(),
                'type' => $predict->getType()
            ]);
        };

        $inlineEdit->onSubmit[] = function ($id, $values): void {
            if (! $predict = $this->predictRepository->find((int) $id)) {
                $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
                $this->redrawControl('flashes');
            }

            $predict->setDestination($values->offsetGet('destination'))
                ->setType($values->offsetGet('type'));
            $this->entityManager->flush();

            $this->flashMessage($this->translator->translate('messages.RowWasEdited'));
            $this->redrawControl('grid');
            $this->redrawControl('flashes');
        };

        $inlineEdit->setShowNonEditingColumns();

        $grid->addAction('deletePredict', '', 'deletePredict!', ['predictId' => 'id'])
            ->setIcon('trash')
            ->setTitle($this->translator->translate('actions.Delete'))
            ->setClass('btn btn-danger waves-effect waves-light ajax')
            ->setConfirmation(
                new StringConfirmation('Opravdu chcete odstranit?')
            );

        return $grid;
    }

    /**
     * @param int $predictId
     * @return void
     */
    public function handleDeletePredict(int $predictId): void
    {
        if (! $predict = $this->predictRepository->find($predictId)) {
            $this->flashMessage($this->translator->translate('errors.RowDoesntExist'));
            $this->redrawControl('flashes');
        }

        $this->entityManager->remove($predict);

        $this->flashMessage($this->translator->translate('messages.Deleted'));
        $this->redrawControl('grid');
        $this->redrawControl('flashes');
    }

}
