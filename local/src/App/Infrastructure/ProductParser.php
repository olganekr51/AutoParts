<?php

namespace App\Infrastructure;

use App\Application\DTO\ProductDTO;
use App\Domain\ProductParserInterface;
use Exception;
use SimpleXMLElement;
use XMLReader;

class ProductParser implements ProductParserInterface
{
    private XMLReader $reader;

    /**
     * @throws Exception
     */
    public function __construct(string $filePath)
    {
        $this->reader = new XMLReader();
        if (!file_exists($filePath) || !$this->reader->open($filePath)) {
            throw new Exception("Не удалось открыть файл: " . $filePath);
        }
    }

    /**
     * @throws Exception
     */
    public function readNextBatch(int $size): array
    {
        $batch = [];
        while (count($batch) < $size && $this->reader->read()) {
            if ($this->reader->nodeType === XMLReader::ELEMENT && $this->reader->name === 'Товар') {
                $node = new SimpleXMLElement($this->reader->readOuterXml());
                $brand = '';
                $compatibility = $images = [];
                if (isset($node->ЗначенияСвойств)) {
                    foreach ($node->ЗначенияСвойств->ЗначениеСвойства as $prop) {
                        if ((string)$prop->Наименование === 'Бренд') {
                            $brand = (string)$prop->Значение;
                        }
                        if ((string)$prop->Наименование === 'Совместимость') {
                            foreach ($prop->Значения->Значение as $val) {
                                $compatibility[] = (string)$val;
                            }
                        }
                        if ((string)$prop->Наименование === 'Фотогалерея') {
                            foreach ($prop->Значения->Значение as $val) {
                                $images[] = (string)$val;
                            }
                        }
                    }
                }

                $batch[] = new ProductDTO(
                    name: (string)$node->Наименование,
                    article: (string)$node->Артикул,
                    price: (float)$node->Цена,
                    quantity: (int)$node->Количество,
                    brandName: $brand,
                    compatibility: $compatibility,
                    images: $images
                );
            }
        }
        return $batch;
    }

    public function close(): void
    {
        $this->reader->close();
    }
}