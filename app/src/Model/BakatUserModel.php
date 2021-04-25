<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BakatUserModel extends Model
{
    protected $table = 'master_bakat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'bakat_id'
    ];
}
