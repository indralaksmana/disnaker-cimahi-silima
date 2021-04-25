<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MinatModel extends Model
{
    protected $table = 'master_minat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany('\App\Model\BkolPencariKerja', 'master_minat_user', 'minat_id', 'user_id');
    }
}
