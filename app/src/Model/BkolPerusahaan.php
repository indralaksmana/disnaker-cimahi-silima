<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BkolPerusahaan extends Model
{
    protected $table = 'b_bkol_perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'jenisusaha_id',
        'provinsi_id',
        'kabupatenkota_id',
        'kecamatan_id',
        'kelurahan_id'
    ];
    public function userperusahaan()
    {
        return $this->belongsTo('App\Model\Users', 'id');
    }
    public function profile()
    {
        return $this->hasOne('\App\Model\UsersProfile', 'user_id', 'id');
    }
    public function jenisusaha()
    {
        return $this->belongsTo('\App\Model\JenisUsahaPost', 'jenis_usaha_id');
    }
    public function produkjasa()
    {
        return $this->belongsTo('\App\Model\ProdukJasaPerusahaanModel', 'perusahaan_id');
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
