<?php

namespace App\Services\Hashing;

use App\Exceptions\HashingException;

abstract class BaseHashingService {

    protected string $hashingAlgorithm;

    public function __construct() {
        $this->hashingAlgorithm = env($this->getEnvPropertyName(), $this->getDefaultHashingAlgorithm());
        if (!$this->isValidHashingAlgorithm($this->hashingAlgorithm)) {
            throw new HashingException('Selected hashing algorithm ' . $this->hashingAlgorithm . ' is not valid. Allowed are only ' . implode(', ', $this->getValidHashingAlgorithms()));
        }
    }

    public function hash(string $data, string $algorithm = null): string {
        if (!isset($algorithm)) {
            $algorithm = $this->hashingAlgorithm;
        }

        return hash($algorithm, $data);
    }

    public function isHashMatching(string $data, string $hash, string $algorithm=null): bool {
        if (!isset($algorithm)) {
            $algorithm = $this->hashingAlgorithm;
        }

        return ($hash === $this->hash($data, $algorithm));
    }

    public function getHashingAlgorithm(): string {
        return $this->hashingAlgorithm;
    }

    protected function isValidHashingAlgorithm($algorithm): bool {
        return in_array($algorithm, $this->getValidHashingAlgorithms());
    }

    abstract protected function getValidHashingAlgorithms(): array;
    abstract protected function getDefaultHashingAlgorithm(): string;
    abstract protected function getEnvPropertyName(): string;
}
