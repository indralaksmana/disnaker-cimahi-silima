<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BlogTags extends Model
{
    protected $table = 'd_blog_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany('\App\Model\BlogPosts', 'd_blog_posts_tags', 'tag_id', 'post_id');
    }
}
