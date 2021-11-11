<?php

namespace App\Services\Approval;

use App\Models\Comment;
use App\Models\Approval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CommentApprovalException;

class ApprovalService
{
    private const DEFAULT_TOKEN_EXP = 24 * 60 * 60;
    private ApprovalTokenService $approvalTokenService;

    public function __construct(ApprovalTokenService $approvalTokenService)
    {
        $this->approvalTokenService = $approvalTokenService;
    }

    public function createApprovalEntry(string $commentId): Approval
    {
        $approval = new Approval();
        $approval->approval_hash = $this->approvalTokenService->generateApprovalToken();
        $approval->comment_id = $commentId;

        $approval->save();
        return $approval;
    }

    public function approve(string $approvalHash): void
    {
        $approval = $this->getApprovalForApprovalHash($approvalHash);
        $this->updateCommentApprovalStatus($approval, Comment::APPROVAL_STATUS['approved']);
    }

    public function decline(string $approvalHash): void
    {
        $approval = $this->getApprovalForApprovalHash($approvalHash);
        $this->updateCommentApprovalStatus($approval, Comment::APPROVAL_STATUS['declined']);
    }

    private function getApprovalForApprovalHash(string $approvalHash): Approval
    {
        $approval = Approval::find($approvalHash);

        if (!isset($approval)) {
            throw new CommentApprovalException('Approval token ' . $approvalHash . 'is invalid');
        }

        if ($this->isTokenExpired($approval->created_at)) {
            throw new CommentApprovalException('Approval token ' . $approvalHash . 'is expired');
        }

        return $approval;
    }

    private function updateCommentApprovalStatus(Approval $approval, int $approvalStatus): void
    {
        DB::transaction(function () use ($approval, $approvalStatus) {
            $comment = Comment::findOrFail($approval->comment_id);
            $comment->approval_status = $approvalStatus;
            $comment->save();
            $approval->delete();
        });
    }

    private function isTokenExpired(Carbon $createdAt): bool
    {
        $secondsToAdd = env('APPROVAL_TOKEN_EXP', self::DEFAULT_TOKEN_EXP);
        $endDate = $createdAt->addSeconds($secondsToAdd);
        return $endDate->lt(Carbon::now());
    }
}
