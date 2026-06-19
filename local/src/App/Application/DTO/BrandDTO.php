<?php

namespace App\Application\DTO;

class BrandDTO
{
    public function __construct(
        public string $name,
        public string $country,
        public string $xmlId,
        public string $logo
    ) {
    }
}