<?php

declare(strict_types=1);

namespace App\Router;

use Nette\Application\Routers\RouteList;
use Nette\StaticClass;

final class RouterFactory
{
    use StaticClass;

    public static function createRouter(): RouteList
    {
        $adminRouter = new RouteList('Admin');
        $adminRouter->addRoute('admin/nastaveni/mena/editace/<id>', 'Settings:Currency:edit');
        $adminRouter->addRoute('admin/nastaveni/feed/', 'Settings:Feed:default');
        $adminRouter->addRoute('admin/nastaveni/feed/editace/<id>', 'Settings:Feed:edit');
        $adminRouter->addRoute('admin/<presenter>/<action>[/<id>]', 'Dashboard:default');

        $apiRouter = new RouteList('Api');
        $apiRouter->addRoute('api/product-xml-splitter', 'App:productXmlSplitter');
        $apiRouter->addRoute('api/download-category', 'App:downloadCategory');
        $apiRouter->addRoute('api/download-product', 'App:downloadProduct');
        $apiRouter->addRoute('api/download-product-all', 'App:downloadProductAll');
        $apiRouter->addRoute('api/download-order', 'App:downloadOrder');
        $apiRouter->addRoute('api/export-orders', 'App:exportOrders');
        $apiRouter->addRoute('api/luigi-feed', 'App:generateLuigisboxFeed');
        $apiRouter->addRoute('api/test', 'App:test');

        $frontRouter = new RouteList('Front');
        $frontRouter->addRoute('registrace', 'Sign:up');
        $frontRouter->addRoute('prihlaseni', 'Sign:in');
        $frontRouter->addRoute('odhlaseni', 'Sign:out');
        $frontRouter->addRoute('<presenter>/<action>[/<id>]', 'Sign:in');

        $router = new RouteList();
        $router->add($adminRouter);
        $router->add($apiRouter);
        $router->add($frontRouter);

        return $router;
    }
}