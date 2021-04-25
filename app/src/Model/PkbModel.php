<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PkbModel extends Model
{
    protected $table = 'pkb';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
    ];
    public function perusahaan()
    {
        return $this->belongsTo('\App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }
}
