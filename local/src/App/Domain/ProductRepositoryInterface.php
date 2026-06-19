<?php

namespace App\Domain;

interface ProductRepositoryInterface
{
    public function findByArticles(array $articles): array;

    public function saveBatch(array $products): array;

    public function deactivate(array $activeArticles): int;
}