<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GolonganJabatanModel extends Model
{
    protected $table = 'master_golonganjabatan';
    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany('\App\Model\JobPosts', 'jabatan_id');
    }

}
