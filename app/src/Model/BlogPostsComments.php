<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BlogPostsComments extends Model
{
    protected $table = 'd_blog_posts_comments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
        'status'
    ];

    public function replies()
    {
        return $this->hasMany('\App\Model\BlogPostsReplies', 'comment_id', 'id');
    }

    public function post()
    {
        return $this->belongsTo('\App\Model\BlogPosts', 'post_id');
    }

    public function approvedReplies()
    {
        return $this->hasMany('\App\Model\BlogPostsReplies', 'comment_id', 'id')->where('status', 1);
    }

    public function pendingReplies()
    {
        return $this->hasMany('\App\Model\BlogPostsReplies', 'comment_id', 'id')->where('status', 0);
    }

    public function user()
    {
        return $this->belongsTo('\App\Model\users', 'user_id');
    }
}
