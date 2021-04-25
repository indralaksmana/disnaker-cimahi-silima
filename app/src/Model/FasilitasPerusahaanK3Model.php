<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FasilitasPerusahaanK3Model extends Model
{
    protected $table = 'fasilitas_perusahaan_k3';
    protected $primaryKey = 'id';
    protected $fillable = [
        'data_perusahaan_id',
        'fpk3_id'
    ];
}
