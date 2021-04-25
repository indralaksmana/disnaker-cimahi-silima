<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PeraturanPerusahaanModel extends Model
{
    protected $table = 'peraturan_perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id',
    ];
    public function perusahaan()
    {
        return $this->belongsTo('\App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }
}
