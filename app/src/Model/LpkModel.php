<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LpkModel extends Model
{
    protected $table = 'c_pp_data_lpk';
    protected $primaryKey = 'id';
    protected $fillable = [

    ];
    public function user()
    {
        return $this->hasMany('App\Model\Users', 'lpk_id', 'id');
    }

    public function programpelatihan()
    {
        return $this->hasMany('\App\Model\DataProgramPelatihanModel', 'lpk_id', 'id');
    }
    public function keterampilan()
    {
        return $this->hasMany('\App\Model\KeterampilanLpkModel', 'lpk_id', 'id');
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
