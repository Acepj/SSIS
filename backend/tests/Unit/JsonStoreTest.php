<?php

declare(strict_types=1);

namespace SSIS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SSIS\Exceptions\StorageException;
use SSIS\Repositories\JsonStore;

final class JsonStoreTest extends TestCase
{
    private string $dir;
    private string $file;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/ssis-store-' . bin2hex(random_bytes(6));
        mkdir($this->dir, 0700, true);
        $this->file = $this->dir . '/records.json';
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->dir);
    }

    public function testReadReturnsEmptyArrayWhenFileIsMissing(): void
    {
        $store = new JsonStore($this->file);

        $this->assertSame([], $store->read());
    }

    public function testWriteThenReadRoundTripsRecords(): void
    {
        $store = new JsonStore($this->file);
        $store->write([['id' => 'a1', 'name' => 'Test']]);

        $this->assertSame([['id' => 'a1', 'name' => 'Test']], $store->read());
    }

    public function testTransactionPersistsMutations(): void
    {
        $store = new JsonStore($this->file);
        $store->write([['id' => 'a1']]);

        $result = $store->transaction(static function (array &$records): void {
            $records[] = ['id' => 'b2'];
        });

        $this->assertCount(2, $result);
        $this->assertCount(2, $store->read());
    }

    public function testFailedTransactionLeavesFileUnchanged(): void
    {
        $store = new JsonStore($this->file);
        $store->write([['id' => 'keep']]);

        try {
            $store->transaction(static function (array &$records): void {
                $records[] = ['id' => 'never'];
                throw new \RuntimeException('boom');
            });
            $this->fail('Expected the transaction to rethrow.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('boom', $exception->getMessage());
        }

        $this->assertSame([['id' => 'keep']], $store->read());
    }

    public function testCorruptFileIsAutomaticallyRecoveredFromBackup(): void
    {
        $store = new JsonStore($this->file);
        $store->write([['id' => 'good1']]); // first state (no backup yet)
        $store->write([['id' => 'good1'], ['id' => 'good2']]); // .bak now holds state 1

        file_put_contents($this->file, '{"broken": ');

        $records = $store->read();

        $this->assertSame([['id' => 'good1']], $records);
        $this->assertIsArray(json_decode((string) file_get_contents($this->file), true));
    }

    public function testCorruptFileWithoutBackupThrowsStorageException(): void
    {
        file_put_contents($this->file, 'definitely not json');

        $store = new JsonStore($this->file);

        $this->expectException(StorageException::class);
        $store->read();
    }

    private function removeDirectory(string $dir): void
    {
        foreach (scandir($dir) ?: [] as $item) {
            if ($item !== '.' && $item !== '..') {
                @unlink($dir . DIRECTORY_SEPARATOR . $item);
            }
        }
        @rmdir($dir);
    }
}
