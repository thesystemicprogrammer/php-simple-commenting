<?php

namespace App\Services\Approval;

class ApprovalTokenRandomBytesService implements ApprovalTokenService
{
    private const DEFAULT_TOKEN_LENGTH = 50;

    public function generateApprovalToken(): string
    {
        $tokenLength = env('APPROVAL_TOKEN_LENGTH', self::DEFAULT_TOKEN_LENGTH); 
        $token = bin2hex(random_bytes(($tokenLength + 1) / 2));
        return substr($token, 0, $tokenLength);
    }
}
