<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PendidikanNonFormalPosts extends Model
{
    protected $table = 'b_resume_pendidikannonformal';
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
