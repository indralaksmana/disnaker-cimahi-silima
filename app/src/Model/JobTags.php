<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JobTags extends Model
{
    protected $table = 'b_job_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany('\App\Model\JobPosts', 'b_job_posts_tags', 'tag_id', 'post_id');
    }
}
