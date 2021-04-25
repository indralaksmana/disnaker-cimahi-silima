<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GalleryCategories extends Model
{
    protected $table = 'd_gallery_categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->hasMany('\App\Model\GalleryPosts', 'category_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
