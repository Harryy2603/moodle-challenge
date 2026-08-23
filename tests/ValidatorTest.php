<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Domain\UserRecord;
use App\Domain\Validator;

class ValidatorTest extends TestCase
{
    public function test_it_passes_valid_records(): void
    {
        $record = new UserRecord();
        $record->name = 'John';
        $record->surname = 'Smith';
        $record->email = 'john@example.com';

        Validator::validate($record);

        $this->assertTrue($record->isValid);
        $this->assertEmpty($record->errors);
    }

    public function test_it_fails_missing_required_fields(): void
    {
        $record = new UserRecord();
        $record->name = ''; // Missing name
        $record->surname = 'Smith';
        $record->email = 'john@example.com';

        Validator::validate($record);

        $this->assertFalse($record->isValid);
        $this->assertContains('Name is required', $record->errors);
    }

    public function test_it_fails_invalid_emails(): void
    {
        $record = new UserRecord();
        $record->name = 'Jane';
        $record->surname = 'Doe';
        $record->email = 'jane@example.com@example.com'; 

        Validator::validate($record);

        $this->assertFalse($record->isValid);
        $this->assertContains('Invalid email format', $record->errors);
    }
}