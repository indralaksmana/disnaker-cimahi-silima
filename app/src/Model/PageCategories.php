<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PageCategories extends Model
{
    protected $table = 'page_categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->hasMany('\App\Model\PagePosts', 'category_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
