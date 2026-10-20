<?php

declare(strict_types=1);

namespace SSIS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SSIS\Exceptions\NotFoundException;
use SSIS\Exceptions\ValidationException;
use SSIS\Repositories\JsonStore;
use SSIS\Repositories\UserRepository;

final class UserRepositoryTest extends TestCase
{
    private string $dir;
    private UserRepository $users;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/ssis-users-' . bin2hex(random_bytes(6));
        mkdir($this->dir, 0700, true);
        $this->users = new UserRepository(new JsonStore($this->dir . '/users.json'));
    }

    protected function tearDown(): void
    {
        foreach (scandir($this->dir) ?: [] as $item) {
            if ($item !== '.' && $item !== '..') {
                @unlink($this->dir . DIRECTORY_SEPARATOR . $item);
            }
        }
        @rmdir($this->dir);
    }

    public function testCreateStoresLowercasedUsernameWithTimestamps(): void
    {
        $user = $this->users->create([
            'username' => 'Test.Admin',
            'password_hash' => password_hash('secret-pass', PASSWORD_DEFAULT),
            'role' => 'admin',
            'active' => true,
            'display_name' => 'Test Admin',
        ]);

        $this->assertNotEmpty($user['id']);
        $this->assertSame('test.admin', $user['username']);
        $this->assertSame('admin', $user['role']);
        $this->assertNotEmpty($user['created_at']);
        $this->assertNotEmpty($user['updated_at']);

        $found = $this->users->findByUsername('TEST.ADMIN');
        $this->assertNotNull($found);
        $this->assertSame($user['id'], $found['id']);
    }

    public function testDuplicateUsernameIsRejectedWithConflict(): void
    {
        $this->users->create([
            'username' => 'taken',
            'password_hash' => 'x',
            'role' => 'student',
        ]);

        try {
            $this->users->create([
                'username' => 'TAKEN',
                'password_hash' => 'y',
                'role' => 'student',
            ]);
            $this->fail('Expected conflict for duplicate username.');
        } catch (ValidationException $exception) {
            $this->assertSame('CONFLICT', $exception->errorCode());
            $this->assertSame(409, $exception->status());
            $this->assertArrayHasKey('username', $exception->fields());
        }
    }

    public function testUpdateChangesFieldsAndBumpsUpdatedAt(): void
    {
        $user = $this->users->create([
            'username' => 'member',
            'password_hash' => 'old',
            'role' => 'student',
        ]);

        $updated = $this->users->update($user['id'], ['password_hash' => 'new', 'active' => false]);

        $this->assertSame('new', $updated['password_hash']);
        $this->assertFalse($updated['active']);
        $this->assertGreaterThanOrEqual(strtotime($user['updated_at']), strtotime($updated['updated_at']));
    }

    public function testUpdateOfUnknownAccountThrowsNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->users->update('usr_missing', ['active' => false]);
    }
}
