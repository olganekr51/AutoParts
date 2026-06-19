<?php

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use App\Application\UseCase\ImportProductsUseCase;
use App\Infrastructure\FileLogger;
use App\Infrastructure\ProductParser;
use App\Infrastructure\ProductRepository;
use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    die("Критическая ошибка: не удалось подключить системные модули iblock или catalog.\n");
}

$xmlPath = $_SERVER['DOCUMENT_ROOT'] . '/upload/1c_import/import.xml';
$logPath = $_SERVER['DOCUMENT_ROOT'] . '/upload/1c_import/log/import_log_' . date('Y-m-d H:i:s') . '.txt';

try {
    $useCase = new ImportProductsUseCase(
        new ProductRepository(),
        new ProductParser($xmlPath),
        new FileLogger($logPath)
    );

    $useCase->execute(500);
} catch (Throwable $e) {
    pre($e);
    file_put_contents(
        $logPath,
        "[" . date('Y-m-d H:i:s') . "] [critical]: КРИТИЧЕСКИЙ СБОЙ: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
}