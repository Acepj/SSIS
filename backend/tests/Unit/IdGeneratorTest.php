<?php

declare(strict_types=1);

namespace SSIS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SSIS\Helpers\IdGenerator;

final class IdGeneratorTest extends TestCase
{
    public function testPublicIdsHavePrefixRandomnessAndAreUnique(): void
    {
        $seen = [];
        for ($i = 0; $i < 100; $i++) {
            $id = IdGenerator::publicId('usr_');
            $this->assertMatchesRegularExpression('/^usr_[0-9a-f]{16}$/', $id);
            $this->assertArrayNotHasKey($id, $seen, 'Duplicate public ID generated');
            $seen[$id] = true;
        }
    }

    public function testRequestCodesFollowFormatAndAvoidAmbiguousCharacters(): void
    {
        $seen = [];
        for ($i = 0; $i < 50; $i++) {
            $code = IdGenerator::requestCode();
            // Alphabet excludes 0/O/1/I - only these characters may appear.
            $this->assertMatchesRegularExpression('/^REQ-\d{4}-[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{6}$/', $code);
            $this->assertArrayNotHasKey($code, $seen, 'Duplicate request code generated');
            $seen[$code] = true;
        }
    }
}
