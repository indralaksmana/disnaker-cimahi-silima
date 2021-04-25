<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JamsostekModel extends Model
{
    protected $table = 'jamsostek';
    protected $primaryKey = 'id';
    protected $fillable = [
        'perusahaan_id'
    ];
    public function perusahaan()
    {
        return $this->belongsTo('App\Model\DataPerusahaanModel', 'perusahaan_id', 'id');
    }
}
