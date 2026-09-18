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
     * @param array $row A row from the CSV file.
     *
     * @return string SQL values string.
     */
    public function convertRow(array $row): string
    {
        $items = array_map(
            static function ($value): string {
                if ($value === '') {
                    return 'NULL';
                }

                if (is_numeric($value)) {
                    return (string) $value;
                }

                return "'" . addslashes($value) . "'";
            },
            $row
        );

        return '(' . implode(', ', $items) . ')';
    }
}
