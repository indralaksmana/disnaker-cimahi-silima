<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ImtaPerpanjanganModel extends Model
{
    protected $table = 'imta_perpanjangan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
    ];

    public function warganegara()
    {
        return $this->belongsTo('\App\Model\NegaraModel', 'warganegara_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo('\App\Model\DataPerusahaanModel', 'perusahaan_id');
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

    public function lokasiprovinsi()
    {
        return $this->belongsTo('\App\Model\Daerah', 'lokasi_provinsi_id');
    }
    public function lokasikabupatenkota()
    {
        return $this->belongsTo('\App\Model\Daerah', 'lokasi_kabupatenkota_id');
    }
}
