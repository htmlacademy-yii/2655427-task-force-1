<?php

declare(strict_types=1);

namespace TaskForce\Import;

use Generator;
use SplFileObject;

/**
 * Reads a CSV file and returns its contents row by row.
 */
class CsvReader
{
    /**
     * Reads data from a CSV file.
     *
     * The first row is used as column headers.
     * Each subsequent row is returned as an associative array.
     *
     * @param string $fileName Path to the CSV file.
     *
     * @return Generator<int, array<string, string|null>>
     */
    public function read(string $fileName): Generator
    {
        $file = new SplFileObject($fileName);

        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY
        );

        $header = null;

        foreach ($file as $row) {
            if ($row === [null]) {
                continue;
            }

            if ($header === null) {
                $header = $row;
                continue;
            }

            yield array_combine($header, $row);
        }
    }
}
