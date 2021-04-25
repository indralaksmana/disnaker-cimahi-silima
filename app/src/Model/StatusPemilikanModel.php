<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StatusPemilikanModel extends Model
{
    protected $table = 'statuspemilikan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function dataperusahaan()
    {
        return $this->hasMany('\App\Model\StatusPemilikanModel', 'status_pemilikan_id');
    }


    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
