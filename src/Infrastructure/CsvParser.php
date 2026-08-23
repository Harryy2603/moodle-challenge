<?php

namespace App\Infrastructure;

use Exception;
use Generator;

class CsvParser
{
    /**
     * Reads a CSV file efficiently using a Generator.
     * @return Generator<array>
     */
    public static function parse(string $filePath): Generator
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("File not found or not readable: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Could not open file: {$filePath}");
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new Exception("CSV file is empty or malformed");
        }

        $header = array_map('trim', array_map('strtolower', $header));

        $nameIdx = array_search('name', $header);
        $surnameIdx = array_search('surname', $header);
        $emailIdx = array_search('email', $header);

        if ($nameIdx === false || $surnameIdx === false || $emailIdx === false) {
            fclose($handle);
            throw new Exception("CSV is missing required headers: name, surname, email");
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;

            yield [
                'name'    => $row[$nameIdx] ?? '',
                'surname' => $row[$surnameIdx] ?? '',
                'email'   => $row[$emailIdx] ?? ''
            ];
        }

        fclose($handle);
    }
}