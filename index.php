<?php

use App\Infrastructure\ProductRepository;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Интернет-магазин «АвтоЗапчасти»");

$repository = new ProductRepository();

?>
    <div class="top_panel top_panel_catalog">
        <section class="promo-banner">
            <div class="promo-container">
                <div class="promo-content">
                    <h1 class="promo-title"><?php
                        $APPLICATION->ShowTitle(false) ?></h1>
                    <a href="/catalog/" class="promo-btn">
                        Перейти в каталог
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    </div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
