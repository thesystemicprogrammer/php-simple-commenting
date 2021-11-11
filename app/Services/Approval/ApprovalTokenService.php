<?php

namespace App\Services\Approval;

interface ApprovalTokenService
{
    public function generateApprovalToken(): string;
}
