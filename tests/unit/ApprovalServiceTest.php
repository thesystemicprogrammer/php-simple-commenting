<?php

namespace Unit;

use TestCase;
use Dotenv\Dotenv;
use App\Models\Approval;
use App\Models\Comment;
use Illuminate\Support\Carbon;
use App\Services\Approval\ApprovalService;
use App\Exceptions\CommentApprovalException;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Services\Approval\ApprovalTokenRandomBytesService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApprovalServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected $approvalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->approvalService = new ApprovalService(new ApprovalTokenRandomBytesService());
    }

    public function testCreateApprovalEntry(): void
    {
        $approval = $this->approvalService->createApprovalEntry(2021);

        $this->assertEquals(50, strlen($approval->approval_hash));
        $this->assertEquals(2021, $approval->comment_id);
    }

    public function testApprove(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();

        $this->approvalService->approve($approval->approval_hash);

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['approved'], $updatedComment->approval_status);
        $this->assertNull(Approval::find($approval->approval_hash));
    }

    public function testApproveInvalidToken(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();
        $this->expectException(CommentApprovalException::class);

        $this->approvalService->approve('nonsense-hash');

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['open'], $updatedComment->approval_status);
        $this->assertNotNull(Approval::find($approval->approval_hash));
    }


    public function testApproveExpiredToken(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();
        Carbon::setTestNow(Carbon::now()->addDay()->addSecond());
        $this->expectException(CommentApprovalException::class);

        $this->approvalService->approve($approval->approval_hash);

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['open'], $updatedComment->approval_status);
        $this->assertNotNull(Approval::find($approval->approval_hash));
    }

    public function testDecline(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();

        $this->approvalService->decline($approval->approval_hash);

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['declined'], $updatedComment->approval_status);
        $this->assertNull(Approval::find($approval->approval_hash));
    }

    public function testDeclineInvalidToken(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();
        $this->expectException(CommentApprovalException::class);

        $this->approvalService->decline('nonsense-hash');

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['open'], $updatedComment->approval_status);
        $this->assertNotNull(Approval::find($approval->approval_hash));
    }


    public function testDeclineExpiredToken(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();
        Carbon::setTestNow(Carbon::now()->addDay()->addSecond());
        $this->expectException(CommentApprovalException::class);

        $this->approvalService->decline($approval->approval_hash);

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['open'], $updatedComment->approval_status);
        $this->assertNotNull(Approval::find($approval->approval_hash));
    }

    public function testApproveEnvExpire(): void
    {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-approval-token-exp.test')->load();
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();
        Carbon::setTestNow(Carbon::now()->addDay()->addSecond());
        
        $this->approvalService->approve($approval->approval_hash);

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['approved'], $updatedComment->approval_status);
        $this->assertNull(Approval::find($approval->approval_hash));
    }

    public function testDeclineEnvExpire(): void
    {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-approval-token-exp.test')->load();
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = $comment->id;
        $approval->save();
        Carbon::setTestNow(Carbon::now()->addDay()->addSecond());
        
        $this->approvalService->decline($approval->approval_hash);

        $updatedComment = Comment::find($comment->id);
        $this->assertEquals(Comment::APPROVAL_STATUS['declined'], $updatedComment->approval_status);
        $this->assertNull(Approval::find($approval->approval_hash));
    }


    public function testDatabaseTransactionCommentAndApproval(): void
    {
        $comment = Comment::factory()->create();
        $approval = Approval::factory()->create();
        $approval->comment_id = 2333333;
        $approval->save();
        $exceptionThrown = false;
        

        try {
            $this->approvalService->approve($approval->approval_hash);
        } catch (ModelNotFoundException $e) {
            $exceptionThrown = true;
        }

        $updatedComment = Comment::find($comment->id);
        $this->assertTrue($exceptionThrown);
        $this->assertEquals(Comment::APPROVAL_STATUS['open'], $updatedComment->approval_status);
        $this->assertNotNull(Approval::find($approval->approval_hash));
    }
}
