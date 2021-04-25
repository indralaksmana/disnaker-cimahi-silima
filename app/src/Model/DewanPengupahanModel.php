<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DewanPengupahanModel extends Model
{
    protected $table = 'dewan_pengupahan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'dewan_pengupahan_id',
    ];

    public function susunananggota()
    {
        return $this->hasMany('\App\Model\DPSusunanAnggotaModel', 'dewan_pengupahan_id', 'id');
    }

    public function sidanganggota()
    {
        return $this->hasMany('\App\Model\DPSidangAnggotaModel', 'dewan_pengupahan_id', 'id');
    }
}
