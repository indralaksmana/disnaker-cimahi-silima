<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FederasiModel extends Model
{
    protected $table = 'federasi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
    ];

    public function provinsi()
    {
        return $this->belongsTo('\App\Model\Daerah', 'provinsi_id');
    }
    public function kabupatenkota()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kabupatenkota_id');
    }
    public function kecamatan()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kecamatan_id');
    }
    public function kelurahan()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kelurahan_id');
    }
    public function serikat()
    {
        return $this->hasMany('\App\Model\SerikatModel', 'federasi_id', 'id');
    }
    public function namaspsb()
    {
        return $this->hasMany('\App\Model\NamaSpSbModel', 'federasi_id', 'id');
    }

}
