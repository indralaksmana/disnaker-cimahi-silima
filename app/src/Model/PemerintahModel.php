<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PemerintahModel extends Model
{
    protected $table = 'data_pemerintah';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'jenis_instansi_id',
    ];
    public function userpemerintah()
    {
        return $this->belongsTo('App\Model\Users', 'id');
    }
    public function instansi()
    {
        return $this->belongsTo('\App\Model\JenisInstansiModel', 'jenis_instansi_id');
    }
    public function jobsposting()
    {
        return $this->hasMany('\App\Model\JobPosts', 'user_id', 'id');
    }
    public function pelatihanposts()
    {
        return $this->hasMany('\App\Model\PelatihanPosts', 'user_id', 'id');
    }
}
