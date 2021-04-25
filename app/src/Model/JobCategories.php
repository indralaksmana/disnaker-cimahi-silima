<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JobCategories extends Model
{
    protected $table = 'b_job_categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->hasMany('\App\Model\JobPosts', 'category_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
