<?php

namespace Unit;

use TestCase;
use Dotenv\Dotenv;
use App\Exceptions\HashingException;
use App\Services\Hashing\EmailHashingService;

class EmailHashingServiceTest extends TestCase {
    protected $hashingService;

    public function testHashDefault(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-default.test')->load();
        $hashingService = new EmailHashingService();    
    
        $hash = $hashingService->hash('test@test.com');

        $this->assertEquals('sha256:::f660ab912ec121d1b1e928a0bb4bc61b15f5ad44d5efdc4e1c92a25e99b8e44a', $hash);
        $this->assertEquals(66 + 7, strlen($hash));
    }

    public function testHashMd5(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-md5.test')->load();
        $hashingService = new EmailHashingService(); 

        $hash = $hashingService->hash('test@test.com');

        $this->assertEquals('md5:::b642b4217b34b1e8d3bd915fc65c4452', $hash);
        $this->assertEquals(32 + 6, strlen($hash));
    }

    public function testHashSha1(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha1.test')->load();
        $hashingService = new EmailHashingService(); 

        $hash = $hashingService->hash('test@test.com');

        $this->assertEquals('sha1:::a6ad00ac113a19d953efb91820d8788e2263b28a', $hash);
        $this->assertEquals(40 + 7, strlen($hash));
    }

    public function testHashSha256(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha256.test')->load();
        $hashingService = new EmailHashingService(); 

        $hash = $hashingService->hash('test@test.com');

        $this->assertEquals('sha256:::f660ab912ec121d1b1e928a0bb4bc61b15f5ad44d5efdc4e1c92a25e99b8e44a', $hash);
        $this->assertEquals(64 + 9, strlen($hash));
    }

    public function testHashSha512(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha512.test')->load();
        $hashingService = new EmailHashingService(); 

        $hash = $hashingService->hash('test@test.com');

        $this->assertEquals('sha512:::045b45ad1b02b7e0de222e19c77a4e765851378a1eb2202e346cde5f979a3217e83454921dfebe3e4e27bb5cc8de91ce68ed75682bd87c1e323146d94c7a2b31', $hash);
        $this->assertEquals(128 + 9, strlen($hash));
    }

    public function testInvalidEmailHashingAlgorithm(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-invalid-email-hash.test')->load();
        $this->expectException(HashingException::class);

        new EmailHashingService();
    }

    public function testMatchEmailMd5(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-md5.test')->load();
        $hashingService = new EmailHashingService(); 

        $isMatch = $hashingService->isHashMatching('test@test.com', 'md5:::b642b4217b34b1e8d3bd915fc65c4452');

        $this->assertTrue($isMatch);
    }

    public function testMatchEmailSha1(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha1.test')->load();
        $hashingService = new EmailHashingService(); 

        $isMatch = $hashingService->isHashMatching('test@test.com', 'sha1:::a6ad00ac113a19d953efb91820d8788e2263b28a');

        $this->assertTrue($isMatch);
    }

    public function testMatchEmailSha256(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha256.test')->load();
        $hashingService = new EmailHashingService(); 

        $isMatch = $hashingService->isHashMatching('test@test.com', 'sha256:::f660ab912ec121d1b1e928a0bb4bc61b15f5ad44d5efdc4e1c92a25e99b8e44a');

        $this->assertTrue($isMatch);
    }

    public function testMatchEmailSha512(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha512.test')->load();
        $hashingService = new EmailHashingService(); 

        $isMatch = $hashingService->isHashMatching('test@test.com', 'sha512:::045b45ad1b02b7e0de222e19c77a4e765851378a1eb2202e346cde5f979a3217e83454921dfebe3e4e27bb5cc8de91ce68ed75682bd87c1e323146d94c7a2b31');

        $this->assertTrue($isMatch);
    }

    public function testNonMatchEmailSha512(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-default.test')->load();
        $hashingService = new EmailHashingService(); 

        $isMatch = $hashingService->isHashMatching('test@test.com', 'sha1:::045b45ad1b02b7e0de222e19c77a4e765851378a1eb2202e346cde5f979a3217e83454921dfebe3e4e27bb5cc8de91ce68ed75682bd87c1e323146d94c7a2b31');

        $this->assertFalse($isMatch);
    }

    public function testExceptionNoAlgorithmPrefix(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-default.test')->load();
        $hashingService = new EmailHashingService(); 
        $this->expectException(HashingException::class);

        $hashingService->isHashMatching('test@test.com', '045b45ad1b02b7e0de222e19c77a4e765851378a1eb2202e346cde5f979a3217e83454921dfebe3e4e27bb5cc8de91ce68ed75682bd87c1e323146d94c7a2b31');
    }

    public function testExceptionEmailWrongAlgorithmPrefix(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-default.test')->load();
        $hashingService = new EmailHashingService(); 
        $this->expectException(HashingException::class);

        $hashingService->isHashMatching('test@test.com', 'xxx:::045b45ad1b02b7e0de222e19c77a4e765851378a1eb2202e346cde5f979a3217e83454921dfebe3e4e27bb5cc8de91ce68ed75682bd87c1e323146d94c7a2b31');
    }
}
