<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PetugasPendataModel extends Model
{
    protected $table = 'petugas_pendata';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
    ];

    public function statuspemilikanposts()
    {
        return $this->hasMany('\App\Model\CompaniesProfile', 'peralatan_k3_id');
    }

    public function nama()
    {
        $query =  $this->select('nama')->get()->pluck('nama');
        return $query;
    }
}
