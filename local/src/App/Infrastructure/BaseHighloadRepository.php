<?php


namespace App\Infrastructure;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ORM\Data\DataManager as ORMDataManager;
use Bitrix\Main\SystemException;


abstract class BaseHighloadRepository
{
    protected const string HL_BLOCK_NAME = '';
    protected string|ORMDataManager $entityDataClass;
    protected int $hlId;

    /**
     * @throws LoaderException
     * @throws SystemException
     */
    public function __construct()
    {
        $this->initializeDataClass(static::HL_BLOCK_NAME);
    }

    /**
     *
     * @throws SystemException|LoaderException
     */
    private function initializeDataClass(string $hlBlockName): void
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new SystemException("Модуль 'highloadblock' не установлен");
        }
        if (empty($hlBlockName)) {
            throw new SystemException("Не указан хайлоад блок.");
        }

        $hlBlock = HighloadBlockTable::getList([
            'filter' => ['=NAME' => $hlBlockName],
        ])->fetch();

        if (!$hlBlock) {
            throw new SystemException("Хайлоад блок с именем '{$hlBlockName}' не найден");
        }

        $this->hlId = (int)$hlBlock['ID'];

        $entity = HighloadBlockTable::compileEntity($hlBlock);

        /** @var DataManager $entityDataClass */
        $this->entityDataClass = $entity->getDataClass();
    }

    public function getEntityDataClass(): ORMDataManager|string
    {
        return $this->entityDataClass;
    }
}