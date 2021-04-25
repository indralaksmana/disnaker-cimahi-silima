<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BlogPosts extends Model
{
    protected $table = 'd_blog_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'slug',
        'content',
        'featured_image',
        'video_provider',
        'video_id',
        'publish_at',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo('\App\Model\BlogCategories', 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany('\App\Model\BlogTags', 'd_blog_posts_tags', 'post_id', 'tag_id');
    }

    public function comments()
    {
        return $this->hasMany('\App\Model\BlogPostsComments', 'post_id', 'id');
    }

    public function replies()
    {
        return $this->hasManyThrough(
            '\App\Model\BlogPostsReplies',
            '\App\Model\BlogPostsComments',
            'post_id',
            'comment_id',
            'id'
        );
    }

    public function approvedComments()
    {
        return $this->hasMany('\App\Model\BlogPostsComments', 'post_id', 'id')->where('status', 1);
    }

    public function pendingComments()
    {
        return $this->hasMany('\App\Model\BlogPostsComments', 'post_id', 'id')->where('status', 0);
    }

    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}
