<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanPostsTags extends Model
{
    protected $table = 'c_pp_pelatihan_posts_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'post_id',
        'tag_id'
    ];
}
