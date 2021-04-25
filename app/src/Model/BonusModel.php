<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BonusModel extends Model
{
    protected $table = 'bonus';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    public function pengupahan()
    {
        return $this->hasOne('\App\Model\BonusModel', 'bonus_id');
    }

    public function name()
    {
        $query =  $this->select('name')->get()->pluck('name');
        return $query;
    }
}
