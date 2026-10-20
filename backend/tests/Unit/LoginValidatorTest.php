<?php

declare(strict_types=1);

namespace SSIS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SSIS\Exceptions\ValidationException;
use SSIS\Validators\LoginValidator;

final class LoginValidatorTest extends TestCase
{
    private readonly LoginValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new LoginValidator();
    }

    public function testAcceptsValidInputAndTrimsIdentifier(): void
    {
        $result = $this->validator->validate([
            'identifier' => '  2026-00123  ',
            'password' => 'secret-pass',
            'remember' => true,
        ]);

        $this->assertSame('2026-00123', $result['identifier']);
        $this->assertSame('secret-pass', $result['password']);
        $this->assertTrue($result['remember']);
    }

    public function testRememberDefaultsToFalse(): void
    {
        $result = $this->validator->validate(['identifier' => 'admin', 'password' => 'secret-pass']);

        $this->assertFalse($result['remember']);
    }

    public function testRejectsMissingIdentifier(): void
    {
        try {
            $this->validator->validate(['password' => 'secret-pass']);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertSame(422, $exception->status());
            $this->assertArrayHasKey('identifier', $exception->fields());
        }
    }

    public function testRejectsIdentifierWithInvalidCharacters(): void
    {
        try {
            $this->validator->validate(['identifier' => 'bad id!', 'password' => 'secret-pass']);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('identifier', $exception->fields());
        }
    }

    public function testRejectsOverlyLongIdentifier(): void
    {
        try {
            $this->validator->validate(['identifier' => str_repeat('a', 65), 'password' => 'secret-pass']);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('identifier', $exception->fields());
        }
    }

    public function testRejectsMissingPassword(): void
    {
        try {
            $this->validator->validate(['identifier' => 'admin']);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->fields());
        }
    }

    public function testRejectsNonStringPassword(): void
    {
        try {
            $this->validator->validate(['identifier' => 'admin', 'password' => 12345]);
            $this->fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->fields());
        }
    }
}
