<?php

namespace App\Services\Avatar;

interface AvatarService {
    public function createAvatar(string $key): string;
}