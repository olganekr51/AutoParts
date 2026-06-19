<?php

namespace App\Infrastructure;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

abstract class BaseIblockRepository
{
    protected const string IBLOCK_NAME = '';
    protected int $iblockId;
    protected array $iblock = [];

    /**
     * @throws LoaderException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function __construct()
    {
        $this->iblockId = $this->getByCode(static::IBLOCK_NAME);
    }

    /**
     * @throws LoaderException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getByCode(string $code): int
    {
        if (isset($this->iblock['by_code'][$code])) {
            return $this->iblock['by_code'][$code];
        }

        if (!Loader::includeModule('iblock')) {
            throw new SystemException("Модуль 'iblock' не установлен");
        }

        if (empty($code)) {
            throw new SystemException("Не указан код инфоблока.");
        }

        $filter = ['=CODE' => $code];

        $iblock = IblockTable::getList(
            [
                'filter' => $filter,
                'select' => ['ID'],
                'limit' => 1,
                'cache' => [
                    'ttl' => 3600000,
                ]
            ]
        )->fetch();

        if (!$iblock) {
            throw new SystemException("Инфоблок с кодом'{$code}' не найден");
        }

        $this->iblock['by_code'][$code] = (int)$iblock['ID'];

        return $this->iblock['by_code'][$code];
    }

    public function getIblockId(): int
    {
        return $this->iblockId;
    }
}