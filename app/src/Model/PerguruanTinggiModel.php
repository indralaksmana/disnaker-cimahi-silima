<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PerguruanTinggiModel extends Model
{
    protected $table = 'c_pp_data_perguruan_tinggi';
    protected $primaryKey = 'id';
    protected $fillable = [

    ];
    public function user()
    {
        return $this->hasMany('App\Model\Users', 'perguruan_tinggi_id', 'id');
    }
    public function programstudi()
    {
        return $this->hasMany('\App\Model\ProgramStudiDiktiModel', 'pt_id', 'id');
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
