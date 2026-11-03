<?php

namespace App\DataTransferObjects;

class FestivalImportResult
{
    /**
     * @param  array<int, array{row: int, message: string}>  $errors
     */
    public function __construct(
        public int $importedCount = 0,
        public int $skippedCount = 0,
        public array $errors = [],
    ) {}

    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function addError(int $row, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'message' => $message,
        ];
    }

    public function incrementImported(): void
    {
        $this->importedCount++;
    }

    public function incrementSkipped(): void
    {
        $this->skippedCount++;
    }
}
