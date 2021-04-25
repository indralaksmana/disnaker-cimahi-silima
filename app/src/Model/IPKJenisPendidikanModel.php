<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class IPKJenisPendidikanModel extends Model
{
    protected $table = 'ipk_jenis_pendidikan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'jenis_pendidikan_id',
    ];
    public function jenispendidikan()
    {
        return $this->belongsTo('App\Model\JenisPendidikanModel', 'jenis_pendidikan_id', 'id');
    }

    public function laporanbulanan()
    {
        return $this->hasMany('\App\Model\IPKJPBulanModel', 'ipk_jp_id');
    }
}
