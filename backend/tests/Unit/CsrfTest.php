<?php

declare(strict_types=1);

namespace SSIS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SSIS\Helpers\Csrf;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsStableWithinOneSession(): void
    {
        $first = Csrf::token();
        $second = Csrf::token();

        $this->assertNotSame('', $first);
        $this->assertSame($first, $second);
    }

    public function testValidateAcceptsTheSessionsOwnToken(): void
    {
        $this->assertTrue(Csrf::validate(Csrf::token()));
    }

    public function testValidateRejectsForeignEmptyAndMissingTokens(): void
    {
        $token = Csrf::token();

        $this->assertFalse(Csrf::validate(str_repeat('0', strlen($token))));
        $this->assertFalse(Csrf::validate(''));
        $this->assertFalse(Csrf::validate(null));
    }

    public function testValidateRejectsTokenFromAnotherSession(): void
    {
        $sessionOneToken = Csrf::token();

        $_SESSION = []; // simulate a different session
        $sessionTwoToken = Csrf::token();

        $this->assertNotSame($sessionOneToken, $sessionTwoToken);
        $this->assertFalse(Csrf::validate($sessionOneToken));
    }
}
