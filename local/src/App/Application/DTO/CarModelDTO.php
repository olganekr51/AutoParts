<?php

namespace App\Application\DTO;

class CarModelDTO
{
    public function __construct(
        public string $name,
        public int $id
    ) {
    }
}