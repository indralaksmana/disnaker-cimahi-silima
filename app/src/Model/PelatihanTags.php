<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanTags extends Model
{
    protected $table = 'c_pp_pelatihan_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany('\App\Model\PelatihanPosts', 'c_pp_pelatihan_posts_tags', 'tag_id', 'post_id');
    }
}
