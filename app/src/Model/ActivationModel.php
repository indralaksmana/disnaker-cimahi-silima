<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ActivationModel extends Model
{
    protected $table = 'activations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo('App\Model\Users', 'user_id', 'id');
    }
}
