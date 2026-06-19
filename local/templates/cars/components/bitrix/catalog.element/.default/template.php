<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

$images = [];
if (!empty($arResult["PREVIEW_PICTURE"])) {
    $images[] = $arResult["PREVIEW_PICTURE"]["SRC"];
}
if (!empty($arResult["DETAIL_PICTURE"])) {
    $images[] = $arResult["DETAIL_PICTURE"]["SRC"];
}
if (!empty($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"])) {
    foreach ($arResult["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $photoId) {
        $images[] = CFile::ResizeImageGet(
                $photoId,
                ["width" => 400, "height" => 400]
        )['src'];
    }
}

if (empty($images)) {
    $images[] = SITE_TEMPLATE_PATH . "/images/no_photo.png";
}
$images = array_unique($images);
$brandXmlId = $arResult["PROPERTIES"]["BRAND"]["VALUE"] ?? '';
$brandName = $arResult['BRAND'];
$compatibility = $arResult['COMPATIBILITY'];
$article = $arResult["PROPERTIES"]["ARTICLE"]["VALUE"] ?? '';

$totalQuantity = $arResult["CATALOG_QUANTITY"] ?? 0;
$inStock = ($totalQuantity > 0);
?>

<div class="product-detail-container" id="<?= $this->GetEditAreaId($arResult['ID']); ?>">
    <div class="product-main-row">
        <div class="product-gallery">
            <div class="swiper product-main-slider">
                <div class="swiper-wrapper">
                    <?php
                    foreach ($images as $imgSrc) { ?>
                        <div class="swiper-slide">
                            <img src="<?= $imgSrc ?>" alt="<?= $arResult["NAME"] ?>">
                        </div>
                        <?php
                    } ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            <?php
            if (count($images) > 1) { ?>
                <div class="swiper product-thumbs-slider">
                    <div class="swiper-wrapper">
                        <?php
                        foreach ($images as $imgSrc) { ?>
                            <div class="swiper-slide">
                                <img src="<?= $imgSrc ?>" alt="<?= $arResult['NAME'] ?>">
                            </div>
                            <?php
                        } ?>
                    </div>
                </div>
                <?php
            } ?>
        </div>
        <div class="product-buy-block js-product-item">
            <h1 class="product-main-title"><?= $arResult["NAME"] ?></h1>

            <div class="product-meta">
                <?php
                if (!empty($article)) { ?>
                    <div class="meta-item"><?= Loc::getMessage("CT_BCS_ARTICLE") ?>: <?= $article ?></div>
                    <?php
                }
                if (!empty($brandName)) { ?>
                    <div class="meta-item"><?= Loc::getMessage("CT_BCS_BRAND") ?>: <?= $brandName ?></div>
                    <?php
                }
                if (!empty($compatibility)) { ?>
                    <div class="meta-item"><?= Loc::getMessage("CT_BCS_COMPABILITY") ?>: <?= $compatibility ?></div>
                    <?php
                } ?>
            </div>
            <div class="product-footer">
                <div class="product-price">
                    <?= $arResult["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"] ?? $arResult["ITEM_PRICES"][0]["PRINT_PRICE"] ?>
                </div>

                <div class="product-actions">
                    <div class="quantity-selector">
                        <button type="button" class="js-qty-minus">-</button>
                        <input type="number" value="1" min="1" max="<?= $inStock ? $totalQuantity : 999 ?>"
                               class="qty-input" id="detail-qty">
                        <button type="button" class="js-qty-plus">+</button>
                    </div>
                    <?php
                    if ($inStock) { ?>
                        <a href="?action=ADD2BASKET&id=<?= $arResult['ID'] ?>"
                           class="btn-buy js-buy-btn" id="detail-buy"><?= Loc::getMessage("CT_BCS_BTN_BUY") ?></a>
                        <?php
                    } ?>
                </div>
                <div class="product-stock-info">
                    <?php
                    if ($inStock) { ?>
                        <span class="stock-status in-stock"><?= Loc::getMessage(
                                    "CT_BCS_IN_STOCK",
                                    ["#QUANTITY#" => $totalQuantity]
                            ) ?></span>
                        <?php
                    } else { ?>
                        <span class="stock-status out-of-stock"><?= Loc::getMessage("CT_BCS_NO_STOCK") ?></span>
                        <?php
                    } ?>
                </div>

            </div>

            <?php
            if ($arResult["DETAIL_TEXT"]) { ?>
                <div class="product-short-desc">
                    <h3><?= Loc::getMessage("CT_BCS_DESC") ?></h3>
                    <div><?= $arResult["DETAIL_TEXT"] ?></div>
                </div>
                <?php
            } ?>
        </div>
    </div>

    <?php
    if (!empty($brandXmlId)) { ?>

        <?php
        global $relatedFilter;
        $relatedFilter = [
                "=PROPERTY_BRAND" => $brandXmlId,
                "!ID" => $arResult["ID"]
        ];

        $APPLICATION->IncludeComponent(
                "bitrix:catalog.section",
                "similar",
                array(
                        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                        "ELEMENT_SORT_FIELD" => "ID",
                        "ELEMENT_SORT_ORDER" => "asc",
                        "PAGE_ELEMENT_COUNT" => "4",
                        "FILTER_NAME" => "relatedFilter",
                        "PRICE_CODE" => $arParams["PRICE_CODE"],
                        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                        "CACHE_TIME" => $arParams["CACHE_TIME"],
                        "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                        "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                        "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                        "SHOW_FROM_SECTION" => "Y"
                ),
                $component
        );
        ?>
        <?php
    } ?>

</div>