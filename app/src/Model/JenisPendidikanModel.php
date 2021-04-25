<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JenisPendidikanModel extends Model
{
    protected $table = 'master_pendidikan';
    public $timestamps = false;

    public function jobpendidikan()
    {
        return $this->hasMany('\App\Model\JobPosts', 'pendidikan_terakhir_id');
    }
    public function pencarikerja()
    {
        return $this->hasMany('\App\Model\BkolPencariKerja', 'user_id');
    }
    public function pencarikerjaLulusan()
    {
        return $this->hasMany('\App\Model\BkolPencariKerja', 'pendidikan_terakhir_id');
    }
    public function jurusanpencarikerja()
    {
        return $this->hasMany('\App\Model\BkolPencariKerja', 'jurusan_pendidikan_id');
    }


}
