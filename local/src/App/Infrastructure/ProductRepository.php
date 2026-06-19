<?php

namespace App\Infrastructure;

use App\Application\Enum\ImportStatus;
use App\Domain\ProductRepositoryInterface;
use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\Model\Product;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Elements\ElementCatalogTable;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CFile;
use CIBlockElement;
use CUtil;
use Exception;

class ProductRepository extends BaseIblockRepository implements ProductRepositoryInterface
{
    public const string IBLOCK_NAME = 'catalog';
    public const string IBLOCK_TYPE = 'catalogs';
    public const int BASE_PRICE_ID = 1;

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function saveBatch(array $products): array
    {
        if (empty($products)) {
            return [];
        }

        $results = [];

        $dictionaries = $this->prepareDictionaries($products);

        $brandsInfoFormat = $dictionaries['brandsMap'];
        $compatibilitiesInfoFormat = $dictionaries['compatibilitiesMap'];

        $existingElements = $this->findByArticles($dictionaries['articles']);

        $el = new CIBlockElement();

        foreach ($products as $product) {
            try {
                $productBrand = $brandsInfoFormat[$product->brandName] ?? null;
                $productCompatibilities = $this->mapCompatibilityIds(
                    $product->compatibility,
                    $compatibilitiesInfoFormat
                );

                if (isset($existingElements[$product->article])) {
                    $current = $existingElements[$product->article];
                    $results[$product->article] = $this->processUpdate(
                        $current,
                        $product,
                        $productBrand,
                        $productCompatibilities,
                        $el
                    );
                } else {
                    $results[$product->article] = $this->processCreate(
                        $product,
                        $productBrand,
                        $productCompatibilities,
                        $el
                    );
                }
            } catch (Exception $e) {
                $results[$product->article] = [
                    'status' => ImportStatus::ERROR->value,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    private function prepareDictionaries(array $products): array
    {
        $articles = $brands = $compatibilities = $brandsInfoFormat = $compatibilitiesInfoFormat = [];

        foreach ($products as $product) {
            $articles[] = $product->article;
            $brands[] = $product->brandName;

            foreach ($product->compatibility as $compatibility) {
                $compatibilities[] = $compatibility;
            }
        }

        $brandsInfo = (new BrandRepository())->getItems(['UF_NAME' => array_unique($brands)]);
        foreach ($brandsInfo as $brandInfo) {
            $brandsInfoFormat[$brandInfo->name] = $brandInfo->xmlId;
        }

        $compatibilitiesInfo = (new CarModelRepository())->getSectionsByNames($compatibilities);
        foreach ($compatibilitiesInfo as $compatibilityInfo) {
            $compatibilitiesInfoFormat[$compatibilityInfo->name] = $compatibilityInfo->id;
        }

        return [
            'articles' => $articles,
            'brandsMap' => $brandsInfoFormat,
            'compatibilitiesMap' => $compatibilitiesInfoFormat,
        ];
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function findByArticles(array $articles): array
    {
        if (empty($articles)) {
            return [];
        }
        $results = $productIds = [];

        $dbRes = ElementCatalogTable::getList([
            'select' => [
                'ID',
                'NAME',
                'ACTIVE',
                'ARTICLE_VALUE' => 'ARTICLE.VALUE',
                'BRAND_VALUE' => 'BRAND.VALUE',
                'COMPATIBILITY_IDS' => 'COMPATIBILITY.IBLOCK_GENERIC_VALUE',
                'MORE_PHOTO_ID' => 'MORE_PHOTO.IBLOCK_GENERIC_VALUE',
            ],
            'filter' => [
                '=ARTICLE_VALUE' => $articles
            ]
        ]);
        while ($row = $dbRes->fetch()) {
            $article = $row['ARTICLE_VALUE'];
            $productIds[] = (int)$row['ID'];

            if (!isset($results[$article])) {
                $results[$article] = [
                    'ID' => (int)$row['ID'],
                    'NAME' => $row['NAME'],
                    'ACTIVE' => $row['ACTIVE'],
                    'ARTICLE' => $row['ARTICLE_VALUE'],
                    'BRAND' => $row['BRAND_VALUE'],
                    'COMPATIBILITY' => [],
                    'MORE_PHOTO' => []
                ];
            }

            if (!empty($row['COMPATIBILITY_IDS'])) {
                $val = (int)$row['COMPATIBILITY_IDS'];
                if (!in_array($val, $results[$article]['COMPATIBILITY'], true)) {
                    $results[$article]['COMPATIBILITY'][] = $val;
                }
            }

            if (!empty($row['MORE_PHOTO_ID'])) {
                $val = (int)$row['MORE_PHOTO_ID'];
                if (!in_array($val, $results[$article]['MORE_PHOTO'], true)) {
                    $fileId = (int)$row['MORE_PHOTO_ID'];
                    $fileName = CFile::GetFileArray($fileId)['ORIGINAL_NAME'];
                    $results[$article]['MORE_PHOTO'][$fileName] = $fileId;
                }
            }
        }

        $catalogRes = ProductTable::getList([
            'select' => [
                'ID',
                'QUANTITY',
                'PRICE_VALUE' => 'PRICE.PRICE'
            ],
            'filter' => [
                '=ID' => $productIds
            ],
            'runtime' => [
                'PRICE' => [
                    'data_type' => PriceTable::class,
                    'reference' => [
                        '=this.ID' => 'ref.PRODUCT_ID',
                        '=ref.CATALOG_GROUP_ID' => new SqlExpression(static::BASE_PRICE_ID)
                    ],
                    'join_type' => 'LEFT'
                ]
            ]
        ]);

        $catalogData = [];
        while ($catRow = $catalogRes->fetch()) {
            $catalogData[(int)$catRow['ID']] = [
                'QUANTITY' => (int)$catRow['QUANTITY'],
                'PRICE' => (float)$catRow['PRICE_VALUE']
            ];
        }
        foreach ($results as $article => $item) {
            $prodId = $item['ID'];
            if (isset($catalogData[$prodId])) {
                $results[$article]['QUANTITY'] = $catalogData[$prodId]['QUANTITY'];
                $results[$article]['PRICE'] = $catalogData[$prodId]['PRICE'];
            }
        }

        return $results;
    }

    private function mapCompatibilityIds($compatibility, array $compatibilitiesInfoFormat): array
    {
        $productCompatibilities = [];

        foreach ($compatibility as $productCompatibility) {
            if (!empty($compatibilitiesInfoFormat[$productCompatibility])) {
                $productCompatibilities[] = $compatibilitiesInfoFormat[$productCompatibility];
            }
        }
        return $productCompatibilities;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    private function processUpdate(
        mixed $current,
        mixed $product,
        mixed $productBrand,
        array $productCompatibilities,
        CIBlockElement $el
    ): array {
        $fieldsToUpdate = [];
        if ($current['NAME'] !== $product->name) {
            $fieldsToUpdate['FIELDS']['NAME'] = $product->name;
        }
        if ($current['ACTIVE'] !== 'Y') {
            $fieldsToUpdate['FIELDS']['ACTIVE'] = 'Y';
        }

        if ($current['BRAND'] !== $productBrand) {
            $fieldsToUpdate['PROPERTY_VALUES']['BRAND'] = $productBrand;
        }

        if ($current["COMPATIBILITY"] != $productCompatibilities) {
            $fieldsToUpdate['PROPERTY_VALUES']['COMPATIBILITY'] = $productCompatibilities;
        }

        if (!empty($product->images)) {
            foreach ($product->images as $image) {
                $imageNames[] = basename($image);
            }
            if (!empty($imageNames) && $imageNames !== $current['MORE_PHOTO']) {
                $fieldsToUpdate['PROPERTY_VALUES']['MORE_PHOTO'] = $this->preparePhotosForSave($product->images);
            }
        }

        if ($current['PRICE'] !== $product->price) {
            $fieldsToUpdate['CATALOG']['PRICE'] = [
                'PRODUCT_ID' => $current['ID'],
                'PRICE' => $product->price,
                'CATALOG_GROUP_ID' => static::BASE_PRICE_ID,
                'CURRENCY' => 'RUB'
            ];
        }

        if ($current['QUANTITY'] !== $product->quantity) {
            $fieldsToUpdate['CATALOG']['QUANTITY'] = ['QUANTITY' => $product->quantity];
        }

        if (!empty($fieldsToUpdate)) {
            if (!empty($fieldsToUpdate['FIELDS'])) {
                $el->Update($current['ID'], $fieldsToUpdate['FIELDS']);
            }

            if (!empty($fieldsToUpdate['PROPERTY_VALUES'])) {
                CIBlockElement::SetPropertyValuesEx(
                    $current['ID'],
                    $this->iblockId,
                    $fieldsToUpdate['PROPERTY_VALUES']
                );
            }
            if (!empty($fieldsToUpdate['CATALOG'])) {
                if (!empty($fieldsToUpdate['CATALOG']['QUANTITY'])) {
                    $productResult = Product::update(
                        $current['ID'],
                        $fieldsToUpdate['CATALOG']['QUANTITY']
                    );
                    if (!$productResult->isSuccess()) {
                        throw new Exception(implode(', ', $productResult->getErrorMessages()));
                    }
                }
                if (!empty($fieldsToUpdate['CATALOG']['PRICE'])) {
                    $dbPrice = PriceTable::getList([
                        'select' => ['ID'],
                        'filter' => [
                            '=PRODUCT_ID' => $current['ID'],
                            '=CATALOG_GROUP_ID' => static::BASE_PRICE_ID,
                        ]
                    ])->fetch();

                    $priceResult = Price::update($dbPrice['ID'], [
                        'fields' => $fieldsToUpdate['CATALOG']['PRICE']
                    ]);

                    if (!$priceResult->isSuccess()) {
                        throw new Exception(
                            "Ошибка обновления цены: " . implode(', ', $priceResult->getErrorMessages())
                        );
                    }
                }
            }

            return ['status' => ImportStatus::UPDATED->value];
        }

        return ['status' => ImportStatus::SKIPPED->value];
    }

    private function preparePhotosForSave(array $xmlPhotoPaths): array
    {
        $propertyValues = [];

        foreach ($xmlPhotoPaths as $path) {
            if (empty($path)) {
                continue;
            }

            $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $path;

            if (file_exists($absolutePath)) {
                $fileArray = CFile::MakeFileArray($absolutePath);
                if ($fileArray) {
                    $propertyValues['n' . count($propertyValues)] = [
                        'VALUE' => $fileArray
                    ];
                }
            }
        }

        return $propertyValues;
    }

    /**
     * @throws Exception
     */
    private function processCreate(
        mixed $product,
        mixed $productBrand,
        array $productCompatibilities,
        CIBlockElement $el
    ): array {
        $productCode = Cutil::translit(
            $product->name,
            "ru",
            ["replace_space" => "-", "replace_other" => "-"]
        );
        $newProductId = $el->Add([
            "IBLOCK_ID" => $this->iblockId,
            "NAME" => $product->name,
            "CODE" => $productCode,
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => [
                "ARTICLE" => $product->article,
                "BRAND" => $productBrand,
                "COMPATIBILITY" => $productCompatibilities,
                "MORE_PHOTO" => $this->preparePhotosForSave($product->images)
            ]
        ]);

        if (!$newProductId) {
            throw new Exception($el->LAST_ERROR);
        }

        $catalogProductFields = [
            'ID' => $newProductId,
            'QUANTITY' => $product->quantity,
        ];

        $productResult = Product::add($catalogProductFields);
        if ($productResult->isSuccess()) {
            $priceResult = Price::add([
                'fields' => [
                    'PRODUCT_ID' => $newProductId,
                    'CATALOG_GROUP_ID' => static::BASE_PRICE_ID,
                    'PRICE' => $product->price,
                    'CURRENCY' => 'RUB'
                ]
            ]);

            if (!$priceResult->isSuccess()) {
                throw new Exception(
                    "Ошибка добавления цены: " . implode(', ', $priceResult->getErrorMessages())
                );
            }
        } else {
            throw new Exception(implode(', ', $productResult->getErrorMessages()));
        }
        return ['status' => ImportStatus::CREATED->value];
    }

    public function deactivate(array $activeArticles): int
    {
        if (empty($activeArticles)) {
            return 0;
        }

        $dbRes = ElementCatalogTable::getList([
            'select' => ['ID'],
            'filter' => [
                '!ARTICLE.VALUE' => $activeArticles,
                '=ACTIVE' => 'Y'
            ]
        ]);

        $el = new CIBlockElement();
        $count = 0;
        while ($item = $dbRes->fetch()) {
            $el->Update($item['ID'], ['ACTIVE' => 'N']);
            $count++;
        }

        return $count;
    }

    public function syncQuantity(int $productId, $quantity): void
    {
        if ($productId <= 0) {
            return;
        }

        CIBlockElement::SetPropertyValuesEx(
            $productId,
            $this->iblockId,
            ['QUANTITY_ON_STOCK' => (int)$quantity]
        );

        Manager::updateElementIndex($this->iblockId, $productId);
    }
}