<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NegaraModel extends Model
{
    protected $table = 'master_negara';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
