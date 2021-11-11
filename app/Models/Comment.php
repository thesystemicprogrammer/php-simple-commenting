<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Services\Avatar\AvatarService;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Comment extends Model {
    use HasFactory;
    use HasRecursiveRelationships;

    public const APPROVAL_STATUS = [
        'open'      => 0,
        'approved'  => 1,
        'declined'  => 2
     ];

    protected $hidden = ['approved'];
    protected $appends = ['avatar'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_hash', 'uri', 'parent_id', 'name', 'email_hash', 'comment', 'approval_status'
    ];

    public function getAvatarAttribute(): string {
        return App::make(AvatarService::class)->createAvatar($this->attributes['name']);
    }

    public function getParentKeyName(): string {
        return 'parent_id';
    }

    protected static function newFactory(): CommentFactory {
        return CommentFactory::new();
    }

}
