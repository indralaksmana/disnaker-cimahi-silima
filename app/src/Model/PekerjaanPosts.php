<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PekerjaanPosts extends Model
{
    protected $table = 'b_resume_pekerjaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'publish_at',
        'status'
    ];


    public function author()
    {
        return $this->hasOne('\App\Model\Users', 'id', 'user_id');
    }
}
