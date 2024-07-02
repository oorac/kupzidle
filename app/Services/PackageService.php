<?php declare(strict_types = 1);

namespace App\Services;

use App\Models\Address;
use App\Models\Package;
use App\Models\Predict;
use App\Models\Repositories\AddressRepository;
use App\Models\Service;
use App\Models\Transaction;
use App\Presenters\AbstractPresenter;
use App\Services\Doctrine\EntityManager;
use App\Utils\Strings;
use DateTime;
use DOMDocument;
use DOMElement;
use DOMXPath;

class PackageService
{
    private const SENDER = [
        'CZ' => [
            'companyName' => 'Kupžidle.cz',
            'contactName' => 'Kupžidle.cz',
            'currency' => 'CZK',
            'contactEmail' => 'obchod@kupzidle.cz'
        ],
        'SK' => [
            'companyName' => 'Kupkresla.sk',
            'contactName' => 'Kupkresla.sk',
            'currency' => 'EUR',
            'contactEmail' => 'obchod@kupkresla.sk'
        ]
    ];

    private const EXCLUDED_ITEM_CODE = ["DOPR B", "DOPR DPD", "DOPR PR", "D", "A", "B", "H", "I", "K", "NS", "S", "Z", "DOP123", "DOPGLS", "MARCR", "NASED 1", "NASED F", "NASED T", "O", "OSOSBNE", "VASED", "Poukaz", "SLEVA", "PLATBA_UNI", "DOPRAVA_UNI"];

    /**
     * @var AddressRepository
     * @inject
     */
    public AddressRepository $addressRepository;

    /**
     * @var EntityManager
     * @inject
     */
    public EntityManager $entityManager;

    public function __construct(
        AddressRepository $addressRepository,
        EntityManager $entityManager
    )
    {
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $response
     * @param array $originData
     * @return array
     */
    public function processResponse(array $response, array $originData): array
    {
        $listTransaction = [];
        $flashMessage['message'] = '';
        if (isset($response['collectionRequestResults'])) {
            foreach ($response['collectionRequestResults'] as $collectionRequestResult) {
                if (isset($collectionRequestResult['errors'])) {
                    foreach ($collectionRequestResult['errors'] as $error) {
                        $flashMessage['message'] .= (! empty($flashMessage['message']) ? ' | ' : '') . 'Chyba kód: ' . $error['errorCode'] . ' - ' . $error['errorContent'] . ' Položka číslo ' . $collectionRequestResult['numOrder'];
                    }

                    $transaction = (new Transaction())
                        ->setTransactionId((string) $response['transactionId'])
                        ->setNumOrder($collectionRequestResult['numOrder'])
                        ->setOriginData($originData);

                    $this->entityManager->persist($transaction);
                    $flashMessage['type'] = AbstractPresenter::FM_ERROR;
                } else {
                    $transaction = (new Transaction())
                        ->setNumOrder($collectionRequestResult['numOrder'])
                        ->setTransactionId($response['transactionId'])
                        ->setODepot($collectionRequestResult['oDepot'])
                        ->setSDepot($collectionRequestResult['sDepot'])
                        ->setOrderNumber($collectionRequestResult['orderNumber'])
                        ->setParcelNumber($collectionRequestResult['parcelNumber'])
                        ->setCollectionRequestId($collectionRequestResult['collectionRequestId'])
                        ->setCollectionRequestStatus($collectionRequestResult['collectionRequestStatus'])
                        ->setOriginData($originData);

                    $this->entityManager->persist($transaction);

                    $flashMessage['message'] = 'Vše v pořádku naimportováno do aplikace DPD sekce sběrný balík';
                    $flashMessage['type'] = AbstractPresenter::FM_SUCCESS;
                }

                $listTransaction[] = $transaction;
            }
        }

        return [$flashMessage, $listTransaction];
    }

    /**
     * @param DOMDocument $dom
     * @param Address $address
     * @return void
     */
    public function processCreateEntities(DOMDocument $dom, Address $address): void
    {
        $xpath = new DOMXPath($dom);
        $orders = $xpath->query('//ObjednavkaPrijataList/ObjednavkaPrijata');

        foreach ($orders as $order) {
            $groupCode = $xpath->query('Group/@Kod', $order)->item(0)->nodeValue;
            $mutation = ($groupCode === 'ESHOPSK') ? Address::COUNTRY_CODE_SK : Address::COUNTRY_CODE_CZ;

            $receiver = $this->createReceiver($order, $xpath, $mutation);
            $package = $this->createPackage($address, $receiver);

            $service = $this->createService($package, $address, $xpath, $order);
            $package->setService($service);

            $this->createPredict($package, Predict::TYPE_SMS, $xpath->query('AdresaKoncovehoPrijemce/Telefon', $order)->item(0)->nodeValue);
            $this->createPredict($package, Predict::TYPE_EMAIL, $xpath->query('AdresaKoncovehoPrijemce/Email', $order)->item(0)->nodeValue);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Package $package
     * @param string $type
     * @param string $destination
     * @return Predict
     */
    public function createPredict(Package $package, string $type, string $destination): Predict
    {
        $predict = (new Predict())
            ->setPackage($package)
            ->setType($type)
            ->setDestination($destination);

        $this->entityManager->persist($predict);

        return $predict;
    }

    /**
     * @param Package $package
     * @param Address $address
     * @param DOMXPath $xpath
     * @param DOMElement $order
     * @return Service
     */
    private function createService(Package $package, Address $address, DOMXPath $xpath, DOMElement $order): Service
    {
        $items = $xpath->query('Polozky/PolozkaObjednavkyPrijate', $order);
        $weight = 0.0;
        $code = "";
        foreach ($items as $item) {
            if (! in_array($xpath->query('Katalog', $item)->item(0)->nodeValue, self::EXCLUDED_ITEM_CODE)) {
                $weight += (float) $xpath->query('CelkovaHmotnost', $item)->item(0)->nodeValue;
                $code .= (! empty($code) ? ' - ' : '') . $xpath->query('Katalog', $item)->item(0)->nodeValue;
            }
        }

        if ($address->getId() === 3) {
            $ref1 = substr($xpath->query('ObejdnavkaVydana_UserData', $order)->item(0)->nodeValue,0,35);
        } else {
            $ref1 = substr(
                    $xpath->query('CisloDokladu', $order)->item(0)->nodeValue,0,35) . ' | ' .
                    substr($xpath->query('ObejdnavkaVydana_UserData', $order)->item(0)->nodeValue,0,35
                );
        }

        $cod = [];
        $zpusobPlatbyNodes = $xpath->query('ZpusobPlatby', $order);

        if ($zpusobPlatbyNodes->length > 0) {
            $kodNodes = $xpath->query('Kod', $zpusobPlatbyNodes->item(0));

            if ($kodNodes->length > 0) {
                $kodValue = $kodNodes->item(0)->nodeValue;
                if ($kodValue === 'D') {
                    $cod = [
                        'amount' => $xpath->query('DetailniRozpisDPH/DetailniRozpisDPH/SumaCelkem', $order)->item(0)->nodeValue,
                        'currency' => $xpath->query('DetailniRozpisDPH/DetailniRozpisDPH/Mena/Kod', $order)->item(0)->nodeValue,
                    ];
                }
            }
        }

        $service = (new Service())
            ->setPackage($package)
            ->setMainServiceElementCodes("001, 013, 402")
            ->setRef1($ref1)
            ->setParcelWeight($weight === 0.0 ? 15 : $weight)
            ->setRef2(substr($code,0,35))
            ->setPickupDate((new DateTime())->modify('+1 day'));

        if (! empty($cod)) {
            $service->setCodCurrency($cod['currency'])
                ->setCodAmount((float) $cod['amount']);
        }

        $this->entityManager->persist($service);

        return $service;
    }

    /**
     * @param Address $sender
     * @param Address $receiver
     * @return Package
     */
    private function createPackage(Address $sender, Address $receiver): Package
    {
        $package = (new Package())
            ->setReceiver($receiver)
            ->setSender($sender)
            ->setCountParcel(1);

        $this->entityManager->persist($package);

        return $package;
    }

    /**
     * @param DOMElement $order
     * @param DOMXPath $xpath
     * @param string $mutation
     * @return Address
     */
    private function createReceiver(DOMElement $order, DOMXPath $xpath, string $mutation): Address
    {
        $city = $xpath->query('AdresaKoncovehoPrijemce/Misto', $order)->item(0)->nodeValue;
        $title = $xpath->query('AdresaKoncovehoPrijemce/KontaktniOsobaNazev', $order)->item(0)->nodeValue;
        $email = $xpath->query('AdresaKoncovehoPrijemce/Email', $order)->item(0)->nodeValue;
        $phone = Strings::webalize($xpath->query('AdresaKoncovehoPrijemce/Telefon', $order)->item(0)->nodeValue, null, false);
        $street = $xpath->query('AdresaKoncovehoPrijemce/Ulice', $order)->item(0)->nodeValue;
        $zipCode = $xpath->query('AdresaKoncovehoPrijemce/PSC', $order)->item(0)->nodeValue;

        if (! $receiver = $this->addressRepository->findOneBy([
            'city' => $city,
            'street' => $street,
            'zipCode' => $zipCode,
            'phone' => $phone,
            'email' => $email,
            'title' => $title,
            'countryCode' => $mutation,
            'contactName' => $title,
            'companyName' => $title,
            'depo' => false
        ])) {
            $receiver = (new Address())
                ->setCity($city)
                ->setTitle($title)
                ->setCompanyName($title)
                ->setContactName($title)
                ->setEmail($email)
                ->setPhone($phone)
                ->setCountryCode($mutation)
                ->setStreet($street)
                ->setZipCode($zipCode);

            $this->entityManager->persist($receiver);
        }

        return $receiver;
    }

    /**
     * @param array $packages
     * @return array
     */
    public function prepareDataForApi(array $packages): array
    {
        $data = [];
        $i = 1;
        /** @var Package $package */
        foreach ($packages as $package) {
            if ($package->getCountParcel() === 1) {
                $data['collectionRequests'][] = $this->setData($package, $i);
                $i++;
            } elseif ($package->getCountParcel() > 1) {
                for ($count = 1; $count <= $package->getCountParcel(); $count++) {
                    $data['collectionRequests'][] = $this->setData($package, $i);
                    $i++;
                }
            }
        }

        return $data;
    }

    private function setData(Package $package, int $i): array
    {
        $array = [
            'numOrder' => $i,
            'sender' => [
                'city' => $package->getSender()->getCity(),
                'name' => $package->getSender()->getCompanyName(),
                'companyName' => $package->getSender()->getCompanyName(),
                'contactEmail' => $package->getSender()->getEmail(),
                'contactMobile' => $package->getSender()->getPhone(),
                'countryCode' => 'CZ',
                'street' => $package->getSender()->getStreet(),
                'zipCode' => $package->getSender()->getZipCode()
            ],
            'receiver' => [
                'city' => $package->getReceiver()->getCity(),
                'name' => $package->getReceiver()->getTitle(),
                'contactName' => $package->getReceiver()->getContactName(),
                'companyName' => $package->getReceiver()->getCompanyName(),
                'contactEmail' => $package->getReceiver()->getEmail(),
                'contactMobile' => $package->getReceiver()->getPhone(),
                'countryCode' => $package->getReceiver()->getCountryCode(),
                'street' => $package->getReceiver()->getStreet(),
                'zipCode' => $package->getReceiver()->getZipCode()
            ]
        ];

        if ($package->getService()->getCodAmount() > 0) {
            $array['service']  = [
                'additionalService' => [
                    'cod' => [
                        'amount' => $package->getService()->getCodAmount(),
                        'currency' => $package->getService()->getCodCurrency(),
                        'reference' => '',
                    ]
                ]
            ];
        }


        if (! $package->getPredicts()->isEmpty()) {
            /** @var Predict $predict */
            foreach ($package->getPredicts() as $predict) {
                $array['service']['additionalService']['predicts'][] =
                    [
                        'destination' => $predict->getDestination(),
                        'destinationType' => '',
                        'language' => '',
                        'trigger' => '',
                        'type' => $predict->getType(),
                    ];
            }
        }

        $array['parcel'] = [
            'weight' => $package->getService()->getParcelWeight()
        ];

        $array['service']['mainServiceCode'] = "";
        $codes = explode(', ', $package->getService()->getMainServiceElementCodes());

        $array['service']['mainServiceElementCodes'] = $codes;
        $array['ref1'] = $package->getService()->getRef1();
        $array['ref2'] = $package->getService()->getRef2();
        $array['pickupDate'] = $package->getService()->getPickupDate()->format('Ymd');

        return $array;
    }
}