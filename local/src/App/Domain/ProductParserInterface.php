<?php

namespace App\Domain;

interface ProductParserInterface
{
    public function readNextBatch(int $size): array;

    public function close(): void;
}