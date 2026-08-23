<?php

namespace App\Services;

use App\Domain\UserRecord;
use App\Domain\Normalizer;
use App\Domain\Validator;
use App\Infrastructure\CsvParser;
use Exception;

class ImportService
{
    public function process(string $filePath, bool $dryRun = false): array
    {
        $results = [
            'total_processed' => 0,
            'total_valid'     => 0,
            'total_invalid'   => 0,
            'records'         => []
        ];

        $seenEmailsInFile = [];

        try {
            $generator = CsvParser::parse($filePath);

            foreach ($generator as $row) {
                $results['total_processed']++;
                $record = new UserRecord();

                $record->name    = Normalizer::normalizeName($row['name']);
                $record->surname = Normalizer::normalizeName($row['surname']);
                $record->email   = Normalizer::normalizeEmail($row['email']);

                Validator::validate($record);

                if ($record->isValid && !empty($record->email)) {
                    if (isset($seenEmailsInFile[$record->email])) {
                        $record->addError("Duplicate email found in CSV file");
                    } else {
                        $seenEmailsInFile[$record->email] = true;
                    }
                }

                if ($record->isValid) {
                    $results['total_valid']++;
                } else {
                    $results['total_invalid']++;
                }

                $results['records'][] = $record;
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return $results;
    }
}