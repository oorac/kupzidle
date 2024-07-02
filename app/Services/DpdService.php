<?php

namespace App\Services;

class DpdService
{

    /**
     * URL API DPD
     */
    private const URL_API = 'https://shipping.dpdgroup.com/api/v1.0/';

//    private const API_KEY = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJOYkJWMnk1VlF0ek1UdG9nb2lJVFZOcTlRemw2TmliSCJ9.YHn-H5nVMQG8wcpfsnZK8q9bpr2WSS25Y0xu5KbNlU0';
    private const API_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJOYkJWMnk1VlF0ek1UdG9nb2lJVFZOcTlRemw2TmliSCJ9.4JTkLPk5lhR_UXmAltXWpqJJ460h6rNJkRPfjgykXzM';

    private const CUSTOMER_ID = '11871165';
    private const BU_CODE = '015';

    /**
     * @param array $data
     * @return array
     */
    public function postDomesticCollection(array $data): array
    {
        $url = 'collections';
        $data['requestorId'] = self::CUSTOMER_ID;
        $data['buCode'] = self::BU_CODE;

        return $this->sendRequest('POST', self::URL_API . $url, $data);
    }

    /**
     * @param array $collectionRequestList
     * @return array
     */
    public function deleteDomesticCollection(array $collectionRequestList): array
    {
        $url = 'collections/cancel';
        $data = [
            'customerId' => self::CUSTOMER_ID,
            'buCode' => self::BU_CODE,
            'collectionRequestList' => $collectionRequestList
        ];

        return $this->sendRequest('PUT', self::URL_API . $url, $data);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $postParams
     * @return array|mixed
     */
    private function sendRequest(string $method, string $url, array $postParams = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            'Authorization: Bearer ' . self::API_KEY
        ));

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (!empty($postParams)) {
            $postParams = json_encode($postParams, JSON_THROW_ON_ERROR);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        }
        $response = curl_exec($ch);
        curl_close($ch);

        if (empty($response)) {
            return [];
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}