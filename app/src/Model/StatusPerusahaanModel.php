<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StatusPerusahaanModel extends Model
{
    protected $table = 'statusperusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function dataperusahaan()
    {
        return $this->hasMany('\App\Model\StatusPerusahaanModel', 'status_perusahaan_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
