<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Domain\Normalizer;

class NormalizerTest extends TestCase
{
    public function test_it_capitalizes_names_correctly(): void
    {
        $this->assertEquals('John', Normalizer::normalizeName('john'));
        $this->assertEquals('Smith', Normalizer::normalizeName('SMITH'));
        $this->assertEquals('Mcdonald', Normalizer::normalizeName('mcDonald'));
        $this->assertEquals('Jane', Normalizer::normalizeName('  jane  ')); 
    }

    public function test_it_lowercases_emails_correctly(): void
    {
        $this->assertEquals('john@example.com', Normalizer::normalizeEmail('JOHN@EXAMPLE.COM'));
        $this->assertEquals('jane@example.com', Normalizer::normalizeEmail('  Jane@Example.com  '));
    }
}