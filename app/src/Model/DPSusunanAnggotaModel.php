<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DPSusunanAnggotaModel extends Model
{
    protected $table = 'dp_susunan_anggota';
    protected $primaryKey = 'id';
    protected $fillable = [
        'dewan_pengupahan_id',
    ];
    public function dewanpengupahan()
    {
        return $this->belongsTo('\App\Model\DewanPengupahanModel', 'dewan_pengupahan_id', 'id');
    }
}
