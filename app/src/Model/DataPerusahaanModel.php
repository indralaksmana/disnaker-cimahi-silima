<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DataPerusahaanModel extends Model
{
    protected $table = 'c_dp_data_perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id'
    ];

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

    public function jenisusaha()
    {
        return $this->belongsTo('\App\Model\JenisUsahaPost', 'jenis_usaha_id', 'id');
    }
    public function statusperusahaan()
    {
        return $this->belongsTo('\App\Model\StatusPerusahaanModel', 'status_perusahaan_id', 'id');
    }
    public function statuspemilikan()
    {
        return $this->belongsTo('\App\Model\StatusPemilikanModel', 'status_pemilikan_id', 'id');
    }
    public function statuspemodalan()
    {
        return $this->belongsTo('\App\Model\StatusPemodalanModel', 'status_pemodalan_id', 'id');
    }
    public function klui()
    {
        return $this->belongsTo('\App\Model\KluiModel', 'klui_id', 'id');
    }
    public function perusahaan()
    {
        return $this->belongsTo('App\Model\Perusahaan', 'perusahaan_id', 'id');
    }
    // public function bonus()
    // {
    //     return $this->belongsTo('\App\Model\BonusModel', 'bonus_id', 'id');
    // }

    // public function thr()
    // {
    //     return $this->belongsTo('\App\Model\ThrModel', 'thr_id', 'id');
    // }

    public function waktukerja()
    {
        return $this->belongsTo('\App\Model\WaktuKerjaModel', 'waktu_kerja_id', 'id');
    }
    public function phiorganisasi()
    {
        return $this->belongsToMany('\App\Model\PerangkatHIOrganisasiModel', 'phio', 'data_perusahaan_id', 'phio_id');
    }
    public function phihubungankerja()
    {
        return $this->belongsToMany('\App\Model\PerangkatHIHubunganKerjaModel', 'phihk', 'data_perusahaan_id', 'phihk_id');
    }

    public function programpensiun()
    {
        return $this->belongsToMany('\App\Model\PensiunModel', 'program_pensiun', 'data_perusahaan_id', 'ppensiun_id');
    }

    public function fasilitask3()
    {
        return $this->belongsToMany('\App\Model\FasilitasK3Model', 'fasilitas_perusahaan_k3', 'data_perusahaan_id', 'fpk3_id');
    }

    public function fasilitaskesejahteraan()
    {
        return $this->belongsToMany('\App\Model\KesejahteraanModel', 'fasilitas_perusahaan_kesejahteraan', 'data_perusahaan_id', 'fkesejahteraan_id');
    }

    public function pkwt()
    {
        return $this->hasMany('\App\Model\PkwtModel', 'perusahaan_id', 'id');
    }
    public function serikat()
    {
        return $this->hasMany('\App\Model\SerikatModel', 'perusahaan_id');
    }
    // public function tags()
    // {
    //     return $this->belongsToMany('\App\Model\BlogTags', 'blog_posts_tags', 'post_id', 'tag_id');
    // }
    public function pengupahan()
    {
        return $this->hasMany('\App\Model\PengupahanModel', 'perusahaan_id', 'id');
    }
    public function jamsostek()
    {
        return $this->hasMany('\App\Model\JamsostekModel', 'perusahaan_id', 'id');
    }
    public function pelanggaran()
    {
        return $this->hasMany('\App\Model\PelanggaranModel', 'perusahaan_id', 'id');
    }
    public function peralatank3()
    {
        return $this->hasMany('\App\Model\PeralatanK3Model', 'perusahaan_id', 'id');
    }


}
