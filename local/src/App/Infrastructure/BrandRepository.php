<?php

namespace App\Infrastructure;

use App\Application\DTO\BrandDTO;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CFile;

class BrandRepository extends BaseHighloadRepository
{
    protected const string HL_BLOCK_NAME = 'Brands';

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getItems(array $filter): array
    {
        $items = [];
        $rsData = $this->entityDataClass::getList([
            'filter' => $filter,
            'order' => ['ID' => 'ASC'],
            'select' => ['*'],
        ]);

        while ($item = $rsData->fetch()) {
            $items[] = (new BrandDTO(
                name: (string)$item['UF_NAME'],
                country: (string)$item['UF_COUNTRY'],
                xmlId: (string)$item['UF_XML_ID'],
                logo: (int)$item['UF_LOGO'] > 0 ? CFile::GetFileArray($item['UF_LOGO'])['SRC'] : '',
            ));
        }

        return $items;
    }
}