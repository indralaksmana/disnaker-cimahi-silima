<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PensiunModel extends Model
{
    protected $table = 'pensiun';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    // public function programpensiun()
    // {
    //     return $this->hasMany('\App\Model\PensiunModel', 'pensiun_id');
    // }
    public function programpensiun()
    {
        return $this->belongsToMany('\App\Model\DataPerusahaanModel', 'program_pensiun', 'data_perusahaan_id', 'ppensiun_id');
    }


    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
