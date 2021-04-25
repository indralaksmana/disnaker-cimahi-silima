<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NamaSpSbModel extends Model
{
    protected $table = 'c_pj_federasi_sp_sb';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'federasi_id'
    ];

    public function serikat()
    {
        return $this->hasMany('\App\Model\SerikatModel', 'serikat_id');
    }

    public function federasi()
    {
        return $this->belongsTo('\App\Model\FederasiModel', 'federasi_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
