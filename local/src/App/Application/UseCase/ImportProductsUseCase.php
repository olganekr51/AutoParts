<?php

namespace App\Application\UseCase;

use App\Application\Enum\ImportStatus;
use App\Domain\ProductParserInterface;
use App\Domain\ProductRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;

readonly class ImportProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private ProductParserInterface $parser,
        private LoggerInterface $logger
    ) {
    }

    public function execute(int $productBatchSize): void
    {
        $startTime = microtime(true);

        $processedArticles = $errors = [];
        $countCreated = $countUpdated = $countDeactivated = 0;

        $this->logger->info("Начало пакетного импорта товаров.");

        while ($productBatch = $this->parser->readNextBatch($productBatchSize)) {
            foreach ($productBatch as $product) {
                $processedArticles[] = $product->article;
            }

            $results = $this->repository->saveBatch($productBatch);

            foreach ($results as $article => $result) {
                if ($result['status'] === ImportStatus::ERROR->value) {
                    $errors[] = [
                        "Ошибка обработки товара",
                        ['article' => $article, 'message' => $result['message']]
                    ];
                } elseif ($result['status'] === ImportStatus::CREATED->value) {
                    $countCreated++;
                } elseif ($result['status'] === ImportStatus::UPDATED->value) {
                    $countUpdated++;
                }
            }
        }
        $this->parser->close();

        try {
            $countDeactivated = $this->repository->deactivate($processedArticles);
        } catch (Exception $e) {
            $errors[] = ["Ошибка деактивации товаров", ['message' => $e->getMessage()]];
        }

        $executionTime = round(microtime(true) - $startTime, 2);
        $this->logger->info(
            "Импорт успешно завершен.",
            [
                'Товаров создано' => $countCreated,
                'Товаров обновлено' => $countUpdated,
                'Товаров деактивировано' => $countDeactivated,
                'Список ошибок' => $errors,
                'Время выполнения' => $executionTime
            ]
        );
    }
}