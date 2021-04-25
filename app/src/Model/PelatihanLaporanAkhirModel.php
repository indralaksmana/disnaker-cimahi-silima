<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PelatihanLaporanAkhirModel extends Model
{
    protected $table = 'pelatihan_laporan_akhir';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pelatihan_id'
    ];

    public function pelatihan()
    {
        return $this->belongsTo('\App\Model\PelatihanPosts', 'pelatihan_id');
    }
}
