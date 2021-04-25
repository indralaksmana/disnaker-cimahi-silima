<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PerusahaanModel extends Model
{
    protected $table = 'perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'jenisusaha_id',
    ];
    public function profile()
    {
        return $this->hasOne('\App\Model\UsersProfile', 'perusahaan_id', 'id');
    }
    public function jenisusaha()
    {
        return $this->belongsTo('\App\Model\JenisUsahaPost', 'jenisusaha_id');
    }
    public function dataperusahaan()
    {
        return $this->hasOne('\App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }
    public function tenagakerja()
    {
        return $this->hasOne('\App\Model\TenagaKerjaModel', 'perusahaan_id', 'id');
    }
    public function jamsostek()
    {
        return $this->hasOne('\App\Model\JamsostekModel', 'perusahaan_id', 'id');
    }
    public function fasilitasperusahaan()
    {
        return $this->hasOne('\App\Model\FasilitasPerusahaanModel', 'perusahaan_id', 'id');
    }
    public function pelanggaran()
    {
        return $this->hasOne('\App\Model\PelanggaranModel', 'perusahaan_id', 'id');
    }
    public function programpensiun()
    {
        return $this->hasOne('\App\Model\ProgramPensiunModel', 'perusahaan_id', 'id');
    }
    public function perangkathi()
    {
        return $this->hasOne('\App\Model\PerangkatHIModel', 'perusahaan_id', 'id');
    }
    public function pengupahan()
    {
        return $this->hasOne('\App\Model\PengupahanModel', 'perusahaan_id', 'id');
    }
    public function peralatank3()
    {
        return $this->hasOne('\App\Model\PeralatanK3Model', 'perusahaan_id', 'id');
    }

}
