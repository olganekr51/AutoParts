<?php

namespace App\Infrastructure;

use App\Application\DTO\CarModelDTO;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class CarModelRepository extends BaseIblockRepository
{
    protected const string IBLOCK_NAME = 'carModels';

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getSectionsByNames(array $names): array
    {
        return $this->getSections([
            '=IBLOCK_ID' => $this->iblockId,
            '=ACTIVE' => 'Y',
            '=NAME' => $names,
        ]);
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getSections(array $filter): array
    {
        $sections = [];

        $dbSections = SectionTable::getList([
            'select' => ['ID', 'NAME'],
            'filter' => $filter,
        ]);

        while ($section = $dbSections->fetch()) {
            $sections[] = new CarModelDTO(
                name: (string)$section['NAME'],
                id: (int)$section['ID']
            );
        }

        return $sections;
    }
}