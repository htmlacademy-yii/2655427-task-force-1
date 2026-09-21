<?php

declare(strict_types=1);

namespace TaskForce\Import;

/**
 * Formats CSV data into an SQL INSERT query.
 */
class SqlConverter
{
    /**
     * Formats a single row into SQL values.
     *
     * @param array<string, string|null> $row A row from the CSV file.
     *
     * @return string SQL values string.
     */
    public function convertRow(array $row): string
    {
        $items = array_map(
            static function (?string $value): string {
                if ($value === '') {
                    return 'NULL';
                }

                if ($value === null) {
                    return 'NULL';
                }

                if (is_numeric($value)) {
                    return $value;
                }

                return "'" . str_replace("'", "''", $value) . "'";
            },
            $row
        );

        return '(' . implode(', ', $items) . ')';
    }
}
