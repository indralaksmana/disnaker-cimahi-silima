<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PengupahanModel extends Model
{
    protected $table = 'pengupahan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id'
    ];

    public function perusahaan()
    {
        return $this->belongsTo('App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }
     public function bonus()
    {
        return $this->belongsTo('\App\Model\BonusModel', 'bonus_id', 'id');
    }

    public function thr()
    {
        return $this->belongsTo('\App\Model\ThrModel', 'thr_id', 'id');
    }
}
