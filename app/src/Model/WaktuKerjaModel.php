<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WaktuKerjaModel extends Model
{
    protected $table = 'c_dp_waktu_kerja';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function waktukerja()
    {
        return $this->hasMany('\App\Model\DataPerusahaaModel', 'waktu_kerja_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
