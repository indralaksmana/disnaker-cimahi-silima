<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JenisUsahaPost extends Model
{
    protected $table = 'c_dp_jenisusaha';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function jenisusaha()
    {
        return $this->hasMany('\App\Model\DataPerusahaaModel', 'jenis_usaha_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
