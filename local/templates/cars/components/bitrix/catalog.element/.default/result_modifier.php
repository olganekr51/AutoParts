<?php

use App\Infrastructure\BrandRepository;
use App\Infrastructure\CarModelRepository;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$brandXmlId = $arResult["PROPERTIES"]["BRAND"]["VALUE"];
$brandName = "";
if (!empty($brandXmlId)) {
    try {
        $brandItems = (new BrandRepository())->getItems(['=UF_XML_ID' => $arResult["PROPERTIES"]["BRAND"]["VALUE"]]);
        $brand = $brandItems[0]->name;
    } catch (Exception $e) {
        $brand = "";
    }
}

$compatibilitiesId = $arResult["PROPERTIES"]["COMPATIBILITY"]["VALUE"];
if (!empty($compatibilitiesId)) {
    try {
        $modelItems = (new CarModelRepository())->getSections(['=ID' => $compatibilitiesId]);
        foreach ($modelItems as $modelItem) {
            $compatibilities[] = $modelItem->name;
        }
    } catch (Exception $e) {
        $compatibilities = [];
    }
}

$arResult['BRAND'] = $brand ?? "";
$arResult['COMPATIBILITY'] = !empty($compatibilities) ? implode(', ', $compatibilities) : "";