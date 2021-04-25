<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BkkModel extends Model
{
    protected $table = 'c_pp_data_bkk';
    protected $primaryKey = 'id';
    protected $fillable = [

    ];
    public function user()
    {
        return $this->hasMany('App\Model\Users', 'bkk_id', 'id');
    }
    public function programkejuruan()
    {
        return $this->hasMany('\App\Model\DataProgramKejuruanModel', 'bkk_id', 'id');
    }
    public function kompetensikeahlian()
    {
        return $this->hasMany('\App\Model\JurusanBkkModel', 'bkk_id', 'id');
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
