<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PagePosts extends Model
{
    protected $table = 'page_posts';
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
        return $this->belongsTo('\App\Model\PageCategories', 'category_id');
    }


    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}
