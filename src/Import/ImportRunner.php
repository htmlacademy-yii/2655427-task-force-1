<?php

declare(strict_types=1);

namespace TaskForce\Import;

use RuntimeException;

/**
 * Imports data from a CSV file into an SQL file.
 */
class ImportRunner
{
    private CsvReader $reader;
    private SqlConverter $converter;

    /**
     * Creates an import runner.
     */
    public function __construct()
    {
        $this->reader = new CsvReader();
        $this->converter = new SqlConverter();
    }

    /**
     * Converts a CSV file into an SQL file.
     *
     * @param string $csvFile Path to the CSV file.
     * @param string $table Database table name.
     * @param string $outputFile Path to the SQL file.
     *
     * @return void
     *
     * @throws RuntimeException If the output file cannot be opened.
     */
    public function run(
        string $csvFile,
        string $table,
        string $outputFile
    ): void {
        $rows = $this->reader->read($csvFile);

        $file = fopen($outputFile, 'w');

        if ($file === false) {
            throw new RuntimeException(
                "Не удалось открыть файл {$outputFile}"
            );
        }

        $firstRow = true;

        foreach ($rows as $row) {
            if ($firstRow) {
                $columns = implode(', ', array_keys($row));

                fwrite(
                    $file,
                    sprintf(
                        "INSERT INTO %s (%s) VALUES\n",
                        $table,
                        $columns
                    )
                );

                $firstRow = false;
            } else {
                fwrite($file, ",\n");
            }

            fwrite(
                $file,
                $this->converter->convertRow($row)
            );
        }

        if (!$firstRow) {
            fwrite($file, ";\n");
        }

        fclose($file);
    }
}
