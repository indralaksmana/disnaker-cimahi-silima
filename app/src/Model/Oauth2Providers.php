<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Oauth2Providers extends Model
{
    protected $table = 'oauth2_providers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'scopes',
        'authorize_url',
        'token_url',
        'resource_url',
        'button',
        'login',
        'status'
    ];

    public function users(){
        return $this->hasManyThrough(
            '\App\Model\Oauth2Users',
            '\App\Model\Users',
            'id',
            'provider_id',
            'id',
            'id'
        );
    }
}
