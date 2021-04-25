<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JenisKompetensiModel extends Model
{
    protected $table = 'master_jenis_kompetensi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'status'
    ];

    public function kompetensiPost()
    {
        return $this->hasMany('\App\Model\KompetensiPosts', 'jenis_kompetensi_id');
    }

}
