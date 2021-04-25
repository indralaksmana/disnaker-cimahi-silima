<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JobPostsTags extends Model
{
    protected $table = 'b_job_posts_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'post_id',
        'tag_id'
    ];
}
