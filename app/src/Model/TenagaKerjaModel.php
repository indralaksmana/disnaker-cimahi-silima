<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TenagaKerjaModel extends Model
{
    protected $table = 'tenaga_kerja';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id'
    ];
    public function perusahaan()
    {
        return $this->belongsTo('App\Model\Perusahaan', 'perusahaan_id', 'id');
    }
}
