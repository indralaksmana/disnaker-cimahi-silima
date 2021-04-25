<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KluiModel extends Model
{
    protected $table = 'c_dp_klui';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_klui',
    ];

    public function dataperusahaan()
    {
        return $this->hasMany('\App\Model\KluiModel', 'klui_id');
    }

    public function no_klui()
    {
        $query =  $this->select('no_klui')->get()->pluck('no_klui');
        return $query;
    }
}
