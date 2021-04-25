<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    protected $table = 'master_daerah';
    public $timestamps = false;

    public function jobprovinsi()
    {
        return $this->hasMany('\App\Model\JobPosts', 'provinsi_id');
    }
    public function bkolperusahaan()
    {
        return $this->hasMany('\App\Model\BkolPerusahaan', 'kabupatenkota_id');
    }
    public function jobkabupatenkota()
    {
        return $this->hasMany('\App\Model\JobPosts', 'kabupatenkota_id');
    }
    public function kecamatanpencaker()
    {
        return $this->hasMany('\App\Model\BkolPencariKerja', 'kecamatan_id');
    }
}
