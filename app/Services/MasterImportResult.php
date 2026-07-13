<?php

namespace App\Services;

class MasterImportResult
{
    /**
     * @param  list<string>  $errors
     * @param  array<string, array{dibuat: int, diperbarui: int}>  $summary
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $errors = [],
        public readonly array $summary = [],
    ) {}

    /**
     * @return list<string>
     */
    public function summaryLines(): array
    {
        $lines = [];
        foreach ($this->summary as $label => $c) {
            $lines[] = "{$label}: {$c['dibuat']} dibuat, {$c['diperbarui']} diperbarui";
        }

        return $lines;
    }
}
