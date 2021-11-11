<?php

namespace Unit;

use TestCase;
use Dotenv\Dotenv;
use App\Models\Comment;
use App\Services\Hashing\PageHashingService;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Services\Hashing\EmailHashingService;
use App\Services\Commenting\CommentingService;

class CommentingServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected $commentingService;
    protected $pageHashingService;
    protected $emailHashingService;

    protected function setUp(): void
    {
        parent::setUp();
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-hash-md5.test')->load();
        $this->pageHashingService = new PageHashingService();
        $this->emailHashingService = new EmailHashingService();
        $this->commentingService = new CommentingService($this->pageHashingService, $this->emailHashingService);
    }

    public function testCreateNewNonModeratedCommentWithParentId(): void
    {
        $requestContentArray = array(
            'uri' => '/home/blog',
            'name' => 'John Test',
            'comment' => 'My test comment',
            'email' => 'test@test.com',
            'parentId' => '1'
        );


        $comment = $this->commentingService->createComment(json_encode($requestContentArray));

        $this->assertEquals(1, Comment::find($comment->id)->count());
        $this->assertEquals($requestContentArray['uri'], $comment->uri);
        $this->assertEquals($requestContentArray['name'], $comment->name);
        $this->assertEquals($requestContentArray['comment'], $comment->comment);
        $this->assertEquals($requestContentArray['parentId'], $comment->parent_id);
        $this->assertEquals(Comment::APPROVAL_STATUS['approved'], $comment->approval_status);
        $this->assertEquals($this->pageHashingService->hash($requestContentArray['uri']), $comment->page_hash);
        $this->assertEquals($this->emailHashingService->hash($requestContentArray['email']), $comment->email_hash);
    }

    public function testCreateNewNonModeratedCommentWithoutParentId(): void
    {
        $requestContentArray = array(
            'uri' => '/home/blog',
            'name' => 'John Test',
            'comment' => 'My test comment',
            'email' => 'test@test.com'
        );

        $comment = $this->commentingService->createComment(json_encode($requestContentArray));

        $this->assertEquals(1, Comment::find($comment->id)->count());
        $this->assertNull($comment->parent_id);
    }

    public function testCreateNewModeratedCommentWithoutParentId(): void
    {
        Dotenv::createUnsafeMutable(dirname(__DIR__), 'env/.env-moderated-comment.test')->load();
        $requestContentArray = array(
            'uri' => '/home/blog',
            'name' => 'John Test',
            'comment' => 'My test comment',
            'email' => 'test@test.com'
        );

        $comment = $this->commentingService->createComment(json_encode($requestContentArray));

        $this->assertEquals(1, Comment::find($comment->id)->count());
        $this->assertEquals(Comment::APPROVAL_STATUS['open'], $comment->approval_status);
    }

    public function testGetCommentTree()
    {
        $comment1 = Comment::factory()->create();
        $comment1_1 = Comment::factory()->create();
        Comment::where('id', $comment1_1->id)->update(['parent_id' => $comment1->id]);
        $comment1_1_1 = Comment::factory()->create();
        Comment::where('id', $comment1_1_1->id)->update(['parent_id' => $comment1_1->id]);
        Comment::factory()->create();

        $tree = $this->commentingService->getCommentTreeForPageHash($comment1->page_hash);

        $this->assertEquals(2, $tree->count());
        $this->assertEquals($comment1_1->id, $tree->first()->children[0]->id);
        $this->assertEquals($comment1_1_1->id, $tree->first()->children[0]->children[0]->id);
    }
}
