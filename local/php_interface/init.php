<?php

use App\Infrastructure\ProductHandler;
use Bitrix\Main\EventManager;

require_once($_SERVER['DOCUMENT_ROOT'] . '/local/src/vendor/autoload.php');

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    'catalog',
    'Bitrix\Catalog\Model\Product::onAfterUpdate',
    [ProductHandler::class, 'onProductSave']
);

$eventManager->addEventHandler(
    'catalog',
    'Bitrix\Catalog\Model\Product::onAfterAdd',
    [ProductHandler::class, 'onProductSave']
);