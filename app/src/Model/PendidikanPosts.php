<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PendidikanPosts extends Model
{
    protected $table = 'b_resume_pendidikan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'category_id',
        'featured_image',
        'publish_at',
        'status'
    ];

    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}
