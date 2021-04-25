<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AK1Model extends Model
{
    protected $table = 'cb_pencaker_pengajuan_ak1';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'jenisusaha_id',
        'file_ktp',
        'file_ijazah',
        'file_sertifikat',
        'file_pengalaman',
        'file_foto'
    ];
    public function user()
    {
        return $this->belongsTo('App\Model\Users');
    }
    public function ak1()
    {
        return $this->hasOne('\App\Model\AK1Model', 'user_id', 'id');
    }
}
