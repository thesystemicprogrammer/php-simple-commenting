<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Services\Avatar\AvatarService;
use Database\Factories\ApprovalFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model {
    use HasFactory;

    protected $primaryKey = 'approval_hash';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'approval_hash', 'comment_id'
    ];

    public function getAvatarAttribute(): string {
        return App::make(AvatarService::class)->createAvatar($this->attributes['name']);
    }

    protected static function newFactory(): ApprovalFactory {
        return ApprovalFactory::new();
    }

}
