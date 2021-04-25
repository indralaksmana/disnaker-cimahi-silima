<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SkillBahasa extends Model
{
    protected $table = 'master_bahasa_skill';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'bahasa_id'
    ];
}
