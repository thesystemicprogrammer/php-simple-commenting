<?php

namespace Unit;

use TestCase;
use Dotenv\Dotenv;
use App\Services\Approval\ApprovalTokenRandomBytesService;

class ApprovalTokenServiceTest extends TestCase
{
    public function testApprovalTokenDefaultLength(): void
    {
        $approvalService = new ApprovalTokenRandomBytesService();
        
        $token = $approvalService->generateApprovalToken();
        
        $this->assertEquals(50, strlen($token));
    }

    public function testApprovalTokenGivenLength(): void
    {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-approval-token.test')->load();
        $approvalService = new ApprovalTokenRandomBytesService();
        $token = $approvalService->generateApprovalToken();

        $this->assertEquals(71, strlen($token));
    }
}
