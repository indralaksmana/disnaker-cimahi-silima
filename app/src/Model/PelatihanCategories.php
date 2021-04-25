<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanCategories extends Model
{
    protected $table = 'c_pp_pelatihan_categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->hasMany('\App\Model\PelatihanPosts', 'category_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
