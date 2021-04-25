<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Bahasa extends Model
{
    protected $table = 'master_bahasa';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function pencarikerja()
    {
        return $this->belongsToMany('\App\Model\BkolPencariKerja', 'master_bahasa_skill', 'bahasa_id', 'user_id');
    }
}
