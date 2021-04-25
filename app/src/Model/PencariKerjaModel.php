<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PencariKerjaModel extends Model
{
    protected $table = 'data_pencari_kerja';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pencarikerja_id'
    ];

    public function petugaspendata()
    {
        return $this->belongsTo('\App\Model\PetugasPendataModel', 'petugas_pendata_id', 'id');
    }

    public function kursuspelatihan()
    {
        return $this->belongsTo('\App\Model\KursusPelatihanModel', 'pelatihan_id', 'id');
    }
    public function bidangkerja()
    {
    	return $this->belongsTo('\App\Model\BidangPekerjaanModel', 'bidang_pekerjaan_id', 'id');
    }

    public function pendidikanterakhir()
    {
    	return $this->belongsTo('\App\Model\JenisPendidikanModel', 'pendidikan_terakhir_id', 'id');
    }
    public function jurusanpendidikan()
    {
    	return $this->belongsTo('\App\Model\JenisPendidikanModel', 'jurusan_id', 'id');
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
