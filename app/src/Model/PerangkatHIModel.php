<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PerangkatHIModel extends Model
{
    protected $table = 'perangkat_hi';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
        'perangkat_o_id',
        'perangkat_HK_id'
    ];
    public function perusahaan()
    {
        return $this->belongsTo('App\Model\Perusahaan', 'perusahaan_id', 'id');
    }
}
