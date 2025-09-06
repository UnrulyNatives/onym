<?php

namespace Blaspsoft\Onym\Tests;

use DateTime;
use InvalidArgumentException;
use Blaspsoft\Onym\Onym;
use PHPUnit\Framework\Attributes\Test;

class OnymTest extends TestCase
{
    protected Onym $onym;

    protected function setUp(): void
    {
        parent::setUp();
        $this->onym = new Onym();
    }

    #[Test]
    public function it_uses_config_defaults()
    {
        $filename = $this->onym->make();
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_random_filenames()
    {
        $filename = $this->onym->random(null, 'txt', ['length' => 8]);
        $this->assertEquals(12, strlen($filename)); // 8 chars + '.txt'
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_random_filenames_with_original_filename()
    {
        $filename = $this->onym->random('test', 'txt', ['length' => 8, 'use_filename' => true]);
        $this->assertStringStartsWith('test_', $filename);
        $this->assertStringEndsWith('.txt', $filename);
        $this->assertEquals(17, strlen($filename)); // 'test_' (5) + 8 chars + '.txt' (4)
    }

    #[Test]
    public function it_generates_uuid_filenames()
    {
        $filename = $this->onym->uuid(null, 'txt');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.txt$/', $filename);
    }

    #[Test]
    public function it_generates_uuid_filenames_with_original_filename()
    {
        $filename = $this->onym->uuid('document', 'txt', ['use_filename' => true]);
        $this->assertStringStartsWith('document_', $filename);
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_timestamp_filenames()
    {
        $now = new DateTime();
        $filename = $this->onym->timestamp('test', 'txt', ['format' => 'Y-m-d_H-i-s']);
        $this->assertStringStartsWith('test_' . $now->format('Y-m-d'), $filename);
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_timestamp_filenames_with_prepend()
    {
        $now = new DateTime();
        $filename = $this->onym->timestamp('test', 'txt', [
            'format' => 'Y-m-d_H-i-s',
            'prepend_timestamp' => true
        ]);
        $this->assertStringStartsWith($now->format('Y-m-d'), $filename);
        $this->assertStringEndsWith('_test.txt', $filename);
    }

    #[Test]
    public function it_generates_date_filenames()
    {
        $now = new DateTime();
        $filename = $this->onym->date('test', 'txt', ['format' => 'Y-m-d']);
        $this->assertStringStartsWith('test_' . $now->format('Y-m-d'), $filename);
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_numbered_filenames()
    {
        $filename = $this->onym->numbered('test', 'txt', ['number' => 5]);
        $this->assertEquals('test_5.txt', $filename);
    }

    #[Test]
    public function it_generates_numbered_filenames_with_padding()
    {
        $filename = $this->onym->numbered('test', 'txt', ['number' => 5, 'pad_length' => 3]);
        $this->assertEquals('test_005.txt', $filename);
    }

    #[Test]
    public function it_generates_slug_filenames()
    {
        $filename = $this->onym->slug('My Document Name', 'txt');
        $this->assertEquals('my-document-name.txt', $filename);
    }

    #[Test]
    public function it_generates_slug_filenames_with_custom_separator()
    {
        $filename = $this->onym->slug('My Document Name', 'txt', ['separator' => '_']);
        $this->assertEquals('my_document_name.txt', $filename);
    }

    #[Test]
    public function it_generates_hash_filenames()
    {
        $filename = $this->onym->hash('test', 'txt', ['algorithm' => 'md5']);
        $this->assertEquals(36, strlen($filename)); // 32 chars MD5 + '.txt'
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_hash_filenames_with_length()
    {
        $filename = $this->onym->hash('test', 'txt', ['algorithm' => 'md5', 'length' => 8]);
        $this->assertEquals(12, strlen($filename)); // 8 chars + '.txt'
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_hash_filenames_with_original_filename()
    {
        $filename = $this->onym->hash('test', 'txt', ['use_filename' => true]);
        $this->assertStringStartsWith('test_', $filename);
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_applies_prefix_and_suffix()
    {
        $filename = $this->onym->random(null, 'txt', [
            'length' => 8,
            'prefix' => 'pre_',
            'suffix' => '_suf'
        ]);
        $this->assertStringStartsWith('pre_', $filename);
        $this->assertStringContainsString('_suf', $filename);
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_validates_length_parameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Length must be between 1 and 255');
        $this->onym->random(null, 'txt', ['length' => 0]);
    }

    #[Test]
    public function it_validates_negative_number()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number must be non-negative');
        $this->onym->numbered('test', 'txt', ['number' => -1]);
    }

    #[Test]
    public function it_validates_invalid_hash_algorithm()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hash algorithm: invalid_algo');
        $this->onym->hash('test', 'txt', ['algorithm' => 'invalid_algo']);
    }

    #[Test]
    public function it_validates_invalid_date_format()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format: @#$%');
        $this->onym->timestamp('test', 'txt', ['format' => '@#$%']);
    }

    #[Test]
    public function it_sanitizes_filenames()
    {
        $filename = $this->onym->make('../malicious/path', 'txt');
        $this->assertStringNotContainsString('../', $filename);
        $this->assertStringNotContainsString('/', $filename);
        $this->assertStringNotContainsString('\\', $filename);
    }

    #[Test]
    public function it_sanitizes_extensions()
    {
        $filename = $this->onym->make('test', '.TXT');
        $this->assertStringEndsWith('.txt', $filename);
    }

    #[Test]
    public function it_generates_unique_filenames_without_collision()
    {
        // Test without setting storage path (no collision detection)
        $filename1 = $this->onym->unique('test', 'txt');
        $filename2 = $this->onym->unique('test', 'txt');
        
        // Without storage path, it should just generate normally
        $this->assertStringEndsWith('.txt', $filename1);
        $this->assertStringEndsWith('.txt', $filename2);
    }

    #[Test]
    public function it_throws_exception_for_unknown_strategy()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown strategy: unknown');
        $this->onym->make('test', 'txt', 'unknown');
    }

    #[Test]
    public function it_can_set_storage_path()
    {
        $result = $this->onym->setStoragePath('/tmp');
        $this->assertInstanceOf(Onym::class, $result);
    }

    #[Test]
    public function it_can_set_max_unique_attempts()
    {
        $result = $this->onym->setMaxUniqueAttempts(5);
        $this->assertInstanceOf(Onym::class, $result);
    }

    #[Test]
    public function it_handles_hash_with_timestamp_for_uniqueness()
    {
        $filename1 = $this->onym->hash('test', 'txt', ['include_timestamp' => true]);
        usleep(1000); // Small delay to ensure different timestamp
        $filename2 = $this->onym->hash('test', 'txt', ['include_timestamp' => true]);
        
        $this->assertNotEquals($filename1, $filename2);
    }

    #[Test]
    public function it_validates_hash_length_bounds()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->onym->hash('test', 'txt', ['algorithm' => 'md5', 'length' => 100]);
    }

    #[Test]
    public function it_uses_default_values_for_empty_inputs()
    {
        // Test with slug strategy to ensure filename is included
        $filename = $this->onym->make('', '', 'slug');
        $this->assertStringEndsWith('.txt', $filename);
        $this->assertStringContainsString('file', $filename);
    }
}