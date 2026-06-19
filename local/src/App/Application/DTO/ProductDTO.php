<?php

namespace App\Application\DTO;

class ProductDTO
{
    public function __construct(
        public string $name,
        public string $article,
        public float $price,
        public int $quantity,
        public string $brandName,
        public array $compatibility,
        public array $images,
    ) {
    }
}