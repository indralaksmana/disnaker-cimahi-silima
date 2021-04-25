<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BlogPostsTags extends Model
{
    protected $table = 'd_blog_posts_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'post_id',
        'tag_id'
    ];
}
