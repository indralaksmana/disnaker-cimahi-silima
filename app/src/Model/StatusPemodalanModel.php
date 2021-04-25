<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StatusPemodalanModel extends Model
{
    protected $table = 'status_pemodalan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function dataperusahaan()
    {
        return $this->hasMany('\App\Model\StatusPemodalanModel', 'status_pemodalan_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
