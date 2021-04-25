<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PerangkatHIOrganisasiModel extends Model
{
    protected $table = 'perangkat_hi_organisasi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
    ];

    // public function phio()
    // {
    //     return $this->hasMany('\App\Model\PerangkatHIOrganisasiModel', 'perangkat_o_id');
    // }

    public function perangkathio()
    {
        return $this->belongsToMany('\App\Model\DataPerusahaanModel', 'phio', 'data_perusahaan_id', 'phio_id');
    }
     public function nama()
    {
        $query =  $this->select('nama')->get()->pluck('nama');
        return $query;
    }
}
