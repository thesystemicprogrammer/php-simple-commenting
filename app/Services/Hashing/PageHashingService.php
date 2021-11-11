<?php

namespace App\Services\Hashing;

class PageHashingService extends BaseHashingService {

    protected function getValidHashingAlgorithms(): array {
        return array('md5', 'sha1', 'sha256');
    }

    protected function getDefaultHashingAlgorithm(): string {
        return 'sha1';
    }

    protected function getEnvPropertyName(): string {
        return 'PAGE_HASHING_ALGORITHM';
    }
}
