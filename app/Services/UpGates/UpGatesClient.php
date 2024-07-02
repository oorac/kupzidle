<?php declare(strict_types = 1);

namespace App\Services\UpGates;

use DateTime;

class UpGatesClient
{
    /**
     * URL API UpGates
     */
    private const URL_API = 'https://zidle.admin.s1.upgates.com/api/v2/';

    private const LOGIN = '88344711';

    private const KEY = 'GYcaYLyHjrAF#SlVCa13';

    private const GET_PRODUCT_WITH_PAGE = 'products?page=%s&active_yn=1';
    private const GET_PRODUCT_WITH_PAGE_AND_LAST_UPDATE = 'products?page=%s&active_yn=1&last_update_time_from=%s';
    private const GET_PRODUCT_BY_PRODUCT_ID = 'products?product_id=%s';
    private const GET_PRODUCT_LABEL_WITH_PAGE = 'products/labels?page=%s&active_yn=1';
    private const GET_PRODUCT_LABEL_WITH_PAGE_AND_LAST_UPDATE = 'products/labels?page=%s&active_yn=1&last_update_time_from=%s';
    private const GET_ORDER_WITH_PAGE = 'orders?creation_time_from=%s&page=%s';
    private const GET_ORDER_BY_ID = 'orders?order_number=%s';
    private const GET_CATEGORY_WITH_PAGE = 'categories?page=%s';
    private const GET_CATEGORY_BY_CATEGORY = 'categories?category_id=%s';
    private const GET_PRODUCT_PARAMETER_WITH_PAGE = 'products/parameters?page=%s&active_yn=1';
    private const GET_PRODUCT_PARAMETER_WITH_PAGE_AND_LAST_UPDATE = 'products/parameters?page=%s&active_yn=1&last_update_time_from=%s';

    /**
     * @param int $page
     * @param bool $all
     * @return array
     */
    public function getProducts(int $page = 1, bool $all = false): array
    {
        if ($all) {
            $url = sprintf(self::GET_PRODUCT_WITH_PAGE, $page);
        } else {
            $url = sprintf(self::GET_PRODUCT_WITH_PAGE_AND_LAST_UPDATE, $page, (new DateTime('-1 hour'))->format('Y-m-d'));
        }

        return $this->sendRequest('GET', self::URL_API . $url);
    }


    /**
     * @param string $productId
     * @return array
     */
    public function getProduct(string $productId): array
    {
        $url = sprintf(self::GET_PRODUCT_BY_PRODUCT_ID, $productId);

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param int $page
     * @param bool $all
     * @return array
     */
    public function getProductLabels(int $page = 1, bool $all = false): array
    {
        if ($all) {
            $url = sprintf(self::GET_PRODUCT_LABEL_WITH_PAGE, $page);
        } else {
            $url = sprintf(self::GET_PRODUCT_LABEL_WITH_PAGE_AND_LAST_UPDATE, $page, (new DateTime('-1 hour'))->format('Y-m-d'));
        }

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param int $page
     * @return array
     */
    public function getOrders(int $page = 1): array
    {
        $url = sprintf(self::GET_ORDER_WITH_PAGE, (new DateTime('-1 day'))->format('Y-m-d'), $page);

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param string $number
     * @return array
     */
    public function getOrderByNumber(string $number): array
    {
        $url = sprintf(self::GET_ORDER_BY_ID, $number);

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param int $page
     * @return array
     */
    public function getCategories(int $page = 1): array
    {
        $url = sprintf(self::GET_CATEGORY_WITH_PAGE, $page);

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getCategoryByCategoryId(int $categoryId): array
    {
        $url = sprintf(self::GET_CATEGORY_BY_CATEGORY, $categoryId);

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param int $page
     * @param bool $all
     * @return array
     */
    public function getProductParameters(int $page = 1, bool $all = false): array
    {
        if ($all) {
            $url = sprintf(self::GET_PRODUCT_PARAMETER_WITH_PAGE, $page);
        } else {
            $url = sprintf(self::GET_PRODUCT_PARAMETER_WITH_PAGE_AND_LAST_UPDATE, $page, (new DateTime('-1 hour'))->format('Y-m-d'));
        }

        return $this->sendRequest('GET', self::URL_API . $url);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $postParams
     * @return array|mixed
     */
    private function sendRequest(string $method, string $url, array $postParams = [])
    {
        $logins = sprintf('%s:%s', self::LOGIN, self::KEY);
        $ch = curl_init($url);
        $basic =  base64_encode($logins);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            'Authorization: Basic ' . $basic
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