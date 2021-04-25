<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PkwtModel extends Model
{
    protected $table = 'pkwt';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
    ];
    public function perusahaan()
    {
        return $this->belongsTo('\App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }
}
