<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HitApiDisdukcapil extends Model
{
    protected $table = 'hit_api_disdukcapil';
    protected $primaryKey = 'id';
    protected $fillable = [
        'hit_access',
        'create',
        'result',
        'content_result'
    ];
    public $timestamps = false;
}
