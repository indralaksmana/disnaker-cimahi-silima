<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UsahaPosts extends Model
{
    protected $table = 'b_resume_usaha';
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
