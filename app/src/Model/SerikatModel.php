<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SerikatModel extends Model
{
    protected $table = 'serikat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
    ];

    public function perusahaan()
    {
        return $this->belongsTo('\App\Model\DataPerusahaanModel', 'perusahaan_id');
    }
    public function serikat()
    {
        return $this->belongsTo('\App\Model\NamaSpSbModel', 'nama_id');
    }
    public function federasi()
    {
        return $this->belongsTo('\App\Model\FederasiModel', 'federasi_id');
    }

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

}
