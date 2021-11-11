<?php

namespace App\Services\Hashing;

use App\Exceptions\HashingException;


class EmailHashingService extends BaseHashingService {

    private const DELIMITER = ':::';

    public function hash(string $data, string $algorithm=null): string {
        if (!isset($algorithm)) {
            $algorithm = $this->hashingAlgorithm;
        }
    
        return $algorithm . self::DELIMITER . hash($algorithm, $data);
    }

    public function isHashMatching(string $data, string $hash, string $algorithm=null): bool {
        if (!str_contains($hash, self::DELIMITER)) {
            throw new HashingException('Hash Matching can only be done with algorithm prefixed hashes if no algorithm is provided');
        }
        
        list($algorithm,) = explode(self::DELIMITER, $hash);
        
        if (!$this->isValidHashingAlgorithm($algorithm)) {
            throw new HashingException('Invalid Hash String Format: ' . $hash);
        }

        return ($hash === $this->hash($data, $algorithm));
    }

    protected function getValidHashingAlgorithms(): array {
        return array('md5', 'sha1', 'sha256', 'sha512');
    }

    protected function getDefaultHashingAlgorithm(): string {
        return 'sha256';
    }

    protected function getEnvPropertyName(): string {
        return 'EMAIL_HASHING_ALGORITHM';
    }
}
