<?php

namespace App\Infrastructure;

use Bitrix\Catalog\Model\Event;
use Local\Catalog\Quantity\Application\QuantityService;

class ProductHandler
{
    public static function onProductSave(Event $event): void
    {
        $fields = $event->getParameter('fields');

        if (!isset($fields['QUANTITY'])) {
            return;
        }

        $id = $event->getParameter('id');
        $productId = is_array($id) ? (int)$id['ID'] : (int)$id;
        (new ProductRepository())->syncQuantity($productId, (int)$fields['QUANTITY']);
    }
}