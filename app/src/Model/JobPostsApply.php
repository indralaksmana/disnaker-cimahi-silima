<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JobPostsApply extends Model
{
    protected $table = 'b_job_posts_apply';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'post_id',
        'coverletter',
        'status'
    ];



    public function post()
    {
        return $this->belongsTo('\App\Model\JobPosts', 'post_id');
    }

  

    public function user()
    {
        return $this->belongsTo('\App\Model\users', 'user_id');
    }
}
