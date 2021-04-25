<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MinatUserModel extends Model
{
    protected $table = 'master_minat_user';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'minat_id'
    ];
}
