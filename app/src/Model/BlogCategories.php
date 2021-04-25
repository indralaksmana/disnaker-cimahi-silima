<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BlogCategories extends Model
{
    protected $table = 'd_blog_categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->hasMany('\App\Model\BlogPosts', 'category_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
