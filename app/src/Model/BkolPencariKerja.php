<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BkolPencariKerja extends Model
{
    protected $table = 'cb_pencaker_ak1';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'iscimahi'
    ];

    public function userpencarikerja()
    {
        return $this->belongsTo('App\Model\Users', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\Model\Users', 'user_id');
    }
    public function pendidikanterakhir()
    {
    	return $this->belongsTo('\App\Model\JenisPendidikanModel', 'pendidikan_terakhir_id', 'id');
    }
    public function jurusanpendidikan()
    {
    	return $this->belongsTo('\App\Model\JenisPendidikanModel', 'jurusan_pendidikan_id', 'id');
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
    public function skillbahasa()
    {
        return $this->belongsToMany('\App\Model\Bahasa', 'skill_bahasa', 'user_id', 'bahasa_id');
    }
    public function minat()
    {
        return $this->belongsToMany('\App\Model\MinatModel', 'minat_user', 'user_id', 'minat_id');
    }
    public function bakat()
    {
        return $this->belongsToMany('\App\Model\BakatModel', 'bakat_user', 'user_id', 'bakat_id');
    }
    public function gaji()
    {
        return $this->belongsTo('\App\Model\GajiModel', 'harapan_gaji_minimum_id');
    }

    public function provinsidalam()
    {
        return $this->belongsTo('\App\Model\Daerah', 'provinsi_dalam_negeri_id');
    }
    public function kabupatenkotadalam()
    {
        return $this->belongsTo('\App\Model\Daerah', 'kabupatenkota_dalam_negeri_id');
    }
    public function negaraluarnegeri()
    {
        return $this->belongsTo('\App\Model\NegaraModel', 'negara_luar_negeri_id');
    }
    public function jabatan()
    {
        return $this->belongsTo('\App\Model\GolonganJabatanModel', 'jenis_jabatan_yang_diminati_id');
    }

    public function perusahaan()
    {
        return $this->hasMany('\App\Model\ShortlistModel', 'pencaker_id', 'id');
    }
    public function dokumen()
    {
        return $this->hasMany('\App\Model\BkolPencariKerjaDokumen', 'pencaker_id', 'user_id');
    }

}
