<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GalleryPosts extends Model
{
    protected $table = 'd_gallery_posts';
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
        return $this->belongsTo('\App\Model\GalleryCategories', 'category_id');
    }


    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}
