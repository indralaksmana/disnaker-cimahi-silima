<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompaniesProfile extends Model
{
    protected $table = 'b_bkol_perusahaan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'jenisusaha_id',
    ];
    public function user()
    {
        return $this->belongsTo('App\Model\Users', 'id');
    }
    public function profile()
    {
        return $this->hasOne('\App\Model\UsersProfile', 'user_id', 'id');
    }
    public function jenisusaha()
    {
        return $this->belongsTo('\App\Model\JenisUsahaPost', 'jenisusaha_id');
    }
}
