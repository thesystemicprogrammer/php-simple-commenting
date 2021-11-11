<?php

namespace app\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalService;
use App\Exceptions\CommentApprovalException;
use App\Services\Commenting\CommentingService;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{

    private CommentingService $commentingService;
    private ApprovalService $approvalService;

    public function __construct(CommentingService $commentingService, ApprovalService $approvalService)
    {
        $this->commentingService = $commentingService;
        $this->approvalService = $approvalService;
    }

    public function getComments($pageHash): JsonResponse
    {
        $commentTree = $this->commentingService->getCommentTreeForPageHash($pageHash);
        return response()->json($commentTree);
    }

    public function createComment(Request $request): JsonResponse
    {
        $this->validate($request, [
            'pageHash' => ['required'],
            'uri' => ['required'],
            'parentId' => ['nullable', 'numeric'],
            'name' => ['required', 'max:255'],
            'email' => ['email'],
            'comment' => ['required', 'max:2048'],
            'terms' => ['accepted']
        ]);

        return DB::transaction(function () use ($request) {
            $comment = $this->commentingService->createComment($request->getContent());
            $this->approvalService->createApprovalEntry($comment->id);
            return response()->json(['message' => 'Comment created', 'id' => $comment->id], Response::HTTP_CREATED);
        });
    }

    public function approve($approvalHash): JsonResponse
    {
        try {
            $comment = $this->approvalService->approve($approvalHash);
            return response()->json(['message' => 'Comment approved!', 'comment' => $comment]);
        } catch (CommentApprovalException $e) {
            return response()->json(['error' => 'Approval failed', 'message' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }

    public function decline($approvalHash): JsonResponse
    {
        try {
            $comment = $this->approvalService->approve($approvalHash);
            return response()->json(['message' => 'Comment declined!', 'comment' => $comment]);
        } catch (CommentApprovalException $e) {
            return response()->json(['error' => 'Rejection failed', 'message' => $e->getMessage(), Response::HTTP_BAD_REQUEST]);
        }
    }
}
