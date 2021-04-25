<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PerangkatHIHubunganKerjaModel extends Model
{
    protected $table = 'perangkat_hi_hubungan_kerja';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
    ];

    public function perangkathihk()
    {
        return $this->belongsToMany('\App\Model\DataPerusahaanModel', 'phihk', 'data_perusahaan_id', 'phihk_id');
    }

    public function nama()
    {
        $query =  $this->select('nama')->get()->pluck('nama');
        return $query;
    }
}
