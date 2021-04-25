<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ThrModel extends Model
{
    protected $table = 'thr';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function pengupahan()
    {
        return $this->hasOne('\App\Model\ThrModel', 'thr_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
