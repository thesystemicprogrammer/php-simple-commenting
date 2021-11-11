<?php

namespace App\Services\Avatar;

use Jdenticon\Identicon;
use App\Services\Avatar\AvatarService;

class IdenticonAvatarService implements AvatarService {

    public function createAvatar(string $key): string {
        $identicon = new Identicon(array(
            'value' => $key,
            'size' => 50
        ));

        return $identicon->getImageData('svg');
    }   
}
