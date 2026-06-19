<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

if (empty($arResult["ITEMS"])) {
    return;
}
?>
    <div class="related-products-section">
        <h2 class="section-title"><?= Loc::getMessage("CP_BC_TPL_RCM_SIMILAR") ?></h2>
        <div class="catalog-products-grid">

            <?php
            foreach ($arResult["ITEMS"] as $arItem) {
                $detailUrl = $arItem["DETAIL_PAGE_URL"];
                $itemName = $arItem["NAME"];
                $itemImg = !empty($arItem["DISPLAY_PROPERTIES"]["MORE_PHOTO"]['VALUE'][0]) ? CFile::ResizeImageGet(
                        $arItem["DISPLAY_PROPERTIES"]["MORE_PHOTO"]['VALUE'][0],
                        ["width" => 400, "height" => 400]
                )['src'] : SITE_TEMPLATE_PATH . "/images/no_photo.png";

                $printPrice = "";
                if (!empty($arItem["ITEM_PRICES"]) && is_array($arItem["ITEM_PRICES"])) {
                    $itemPrice = reset($arItem["ITEM_PRICES"]);
                    $printPrice = $itemPrice["PRINT_PRICE"];
                }

                $isAvailable = ($arItem["CATALOG_QUANTITY"] > 0 || $arItem["CATALOG_CAN_BUY_ZERO"] === "Y");
                ?>
                <div class="js-product-item product-card <?= !$isAvailable ? 'out-of-stock' : '' ?>"
                     id="<?= $this->GetEditAreaId($arItem['ID']); ?>">

                    <a href="<?= $detailUrl ?>" class="product-img-wrap" data-skip-moving="true">
                        <img src="<?= $itemImg ?>" alt="<?= $itemName ?>">
                    </a>

                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="<?= $detailUrl ?>" data-skip-moving="true"><?= $itemName ?></a>
                        </h3>

                        <div class="product-stock-info">
                            <?php
                            if ($isAvailable) { ?>
                                <span class="stock-status in-stock">
                            <?= Loc::getMessage("CT_BCS_IN_STOCK", ["#QUANTITY#" => (int)$arItem["CATALOG_QUANTITY"]]
                            ) ?>
                        </span>
                                <?php
                            } else { ?>
                                <span class="stock-status no-stock">
                            <?= Loc::getMessage("CT_BCS_NO_STOCK") ?>
                        </span>
                                <?php
                            } ?>
                        </div>

                        <div class="product-footer">
                            <div class="product-price">
                                <?= !empty($printPrice) ? $printPrice : Loc::getMessage("CT_BCS_PRICE_UPON_REQUEST") ?>
                            </div>

                            <div class="product-actions">
                                <?php
                                if ($isAvailable) { ?>
                                    <a href="<?= $detailUrl ?>?action=ADD2BASKET&id=<?= $arItem['ID'] ?>"
                                       class="btn-buy js-buy-btn"><?= Loc::getMessage("CT_BCS_BTN_BUY") ?></a>
                                    <?php
                                } else { ?>
                                    <a href="<?= $detailUrl ?>" class="btn-more">
                                        <?= Loc::getMessage("CT_BCS_BTN_MORE") ?>
                                    </a>
                                    <?php
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            } ?>
        </div>
    </div>
<?php