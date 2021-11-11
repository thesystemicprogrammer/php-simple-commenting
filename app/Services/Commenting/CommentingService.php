<?php

namespace App\Services\Commenting;

use App\Models\Comment;
use Illuminate\Support\Collection;
use App\Services\Hashing\EmailHashingService;
use App\Services\Hashing\PageHashingService;

class CommentingService
{
    private PageHashingService $pageHashingService;
    private EmailHashingService $emailHashingService;

    public function __construct(PageHashingService $pageHashingService, EmailHashingService $emailHashingService)
    {
        $this->pageHashingService = $pageHashingService;
        $this->emailHashingService = $emailHashingService;
    }

    public function getCommentTreeForPageHash(string $pageHash): Collection
    {
        $selection = function ($query) use ($pageHash) {
            $query->where('page_hash', $pageHash)->whereNull('parent_id');
        };

        return Comment::treeOf($selection)->get()->toTree();
    }

    public function createComment(string $requestContent): Comment
    {
        $requestContentJson = json_decode($requestContent);

        $comment = new Comment();
        $comment->uri = $requestContentJson->uri;
        $comment->name = $requestContentJson->name;
        $comment->comment = $requestContentJson->comment;
        $comment->email_hash = $this->emailHashingService->hash($requestContentJson->email);
        $comment->page_hash = $this->pageHashingService->hash($requestContentJson->uri);

        if (env('COMMENTS_MODERATED')) {
            $comment->approval_status = Comment::APPROVAL_STATUS['open'];
        } else {
            $comment->approval_status = Comment::APPROVAL_STATUS['approved'];
        }

        if (isset($requestContentJson->parentId)) {
            $comment->parent_id = $requestContentJson->parentId;
        }

        $comment->save();
        return $comment;
    }
}
