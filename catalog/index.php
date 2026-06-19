<?php

use App\Infrastructure\ProductRepository;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Каталог автозапчастей");

$APPLICATION->IncludeComponent(
    "bitrix:breadcrumb",
    "",
    [
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => "s1"
    ]
);

$repository = new ProductRepository();
$APPLICATION->IncludeComponent(
    "bitrix:catalog",
    ".default",
    array(
        "IBLOCK_TYPE" => $repository::IBLOCK_TYPE,
        "IBLOCK_ID" => $repository->getIblockId(),
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/catalog/",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
        "CACHE_FILTER" => "Y",
        "PAGE_ELEMENT_COUNT" => "8",
        "PRICE_CODE" => ["BASE"],
        "LIST_PROPERTY_CODE" => ["MORE_PHOTO"],
        "USE_PRICE_COUNT" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "USE_PRODUCT_QUANTITY" => "Y",
        "USE_FILTER" => "Y",
        "FILTER_NAME" => "arrFilter",
        "SEF_URL_TEMPLATES" => [
            "sections" => "",
            "section" => "",
            "element" => "item/#ELEMENT_CODE#/",
            "smart_filter" => "filter/#SMART_FILTER_PATH#/apply/",

        ],
        "ADD_SECTIONS_CHAIN" => "Y",
        "ADD_ELEMENT_CHAIN" => "Y",
    ),
    false
);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");