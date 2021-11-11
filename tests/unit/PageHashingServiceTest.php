<?php

namespace Unit;

use TestCase;
use Dotenv\Dotenv;
use App\Exceptions\HashingException;
use App\Services\Hashing\PageHashingService;

class PageHashingServiceTest extends TestCase {
    protected $hashingService;

    public function testHashDefault(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-default.test')->load();
        $hashingService = new PageHashingService();    
    
        $hash = $hashingService->hash('/home/blog/article-2021-11-04');

        $this->assertEquals('874c7ca324710d66a777354997bf78039361ea65', $hash);
        $this->assertEquals(40, strlen($hash));
    }

    public function testHashMd5(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-md5.test')->load();
        $hashingService = new PageHashingService(); 

        $hash = $hashingService->hash('/home/blog/article-2021-11-04');

        $this->assertEquals('61d55c1e7d6ece6f47d0f2bd1fefa6b3', $hash);
        $this->assertEquals(32, strlen($hash));
    }

    public function testHashSha1(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha1.test')->load();
        $hashingService = new PageHashingService(); 

        $hash = $hashingService->hash('/home/blog/article-2021-11-04');

        $this->assertEquals('874c7ca324710d66a777354997bf78039361ea65', $hash);
        $this->assertEquals(40, strlen($hash));
    }

    public function testHashSha256(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha256.test')->load();
        $hashingService = new PageHashingService(); 

        $hash = $hashingService->hash('/home/blog/article-2021-11-04');

        $this->assertEquals('adb4493c424080dd18311942792d809081d6f5167fc8cbc02398944776a47d14', $hash);
        $this->assertEquals(64, strlen($hash));
    }

    public function testExceptionWithHashSha512(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha512.test')->load();
        $this->expectException(HashingException::class);

        new PageHashingService(); 
    }

    public function testInvalidEmailHashingAlgorithm(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-invalid-email-hash.test')->load();
        $this->expectException(HashingException::class);

        new PageHashingService();
    }

    public function testMatchUriMd5(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-md5.test')->load();
        $hashingService = new PageHashingService(); 

        $isMatch = $hashingService->isHashMatching('/home/blog/another-article-2021-10-29', '54ec210ed6aca3c3c6bb4a5ef892d192');

        $this->assertTrue($isMatch);
    }

    public function testMatchUriSha1(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha1.test')->load();
        $hashingService = new PageHashingService(); 

        $isMatch = $hashingService->isHashMatching('/home/blog/another-article-2021-10-29', '0f87cb9b598dda51c3fa410a6f34ece881cd43a4');

        $this->assertTrue($isMatch);
    }

    public function testMatchUriSha256(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-sha256.test')->load();
        $hashingService = new PageHashingService(); 

        $isMatch = $hashingService->isHashMatching('/home/blog/another-article-2021-10-29', 'dffa5f787f874452bfc79991aa57396a57ca9333ccee9075d7e091fe0148f20c');

        $this->assertTrue($isMatch);
    }

    public function testNonMatchUri(): void {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-default.test')->load();
        $hashingService = new PageHashingService(); 

        $isMatch = $hashingService->isHashMatching('/home/blog/another-article-2021-10-29m', '045b45ad1b02b7e0de222e19c77a4e765851378a1eb2202e346cde5f979a3217e83454921dfebe3e4e27bb5cc8de91ce68ed75682bd87c1e323146d94c7a2b31');

        $this->assertFalse($isMatch);
    }
}
