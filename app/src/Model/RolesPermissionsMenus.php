<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RolesPermissionsMenus extends Model
{
    protected $table = 'roles_permissions_menu';
    protected $primaryKey = 'id';
    protected $fillable = [
        'slug',
        'name'
    ];
}
