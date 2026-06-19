<?php

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    global $APPLICATION;
    $APPLICATION->ShowHead(); ?>
    <title><?php
        $APPLICATION->ShowTitle(); ?></title>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, shrink-to-fit=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="format-detection" content="telephone=no"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <?php
    Asset::getInstance()->addCss("/bitrix/css/main/bootstrap.css");
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/swiper-bundle.min.css");

    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/jquery-4.0.0.min.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/swiper-bundle.min.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/jquery-4.0.0.min.js");
    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/script.js");

    ?>
</head>
<body>
<div id="panel">
    <?php
    $APPLICATION->ShowPanel(); ?>
</div>
<header class="header-container">
    <a href="/" class="header-logo">
        <span>Auto</span>Parts
    </a>

    <nav class="header-nav">
        <a href="/catalog/">Каталог</a>
        <a href="#">О компании</a>
        <a href="#">Доставка</a>
        <a href="#">Контакты</a>
    </nav>
    <div class="header-actions">
        <a href="tel:+78005553535" class="header-phone">+7 (800) 555-35-35</a>
        <a href="#" class="header-cart"> Корзина </a>
    </div>
</header>
<div class="container">
	
						