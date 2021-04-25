<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RolesPermissions extends Model
{
    protected $table = 'roles_permissions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'slug',
        'name'
    ];
}
