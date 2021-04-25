<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BakatModel extends Model
{
    protected $table = 'master_bakat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany('\App\Model\BkolPencariKerja', 'master_bakat_user', 'bakat_id', 'user_id');
    }
}
