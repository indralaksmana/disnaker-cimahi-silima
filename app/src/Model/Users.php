<?php
namespace App\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class Users extends EloquentUser
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'username',
        'bkk_id',
        'lpk_id',
        'perguruan_tinggi_id',
        'perusahaan_id',
        'password',
        'last_name',
        'first_name',
        'permissions',
        'nik',
        'fullname',
        'companyname',
        'companysaddress',
        'placeofbirth',
        'dateofbirth',
        'monthofbirth',
        'yearofbirth',
        'gender',
        'religion',
        'nationality',
        'address',
        'districts',
        'maritalstatus',
        'lasteducation',
        'address',
        'districts',
        'phonenumber',
        'photoprofile',
        'expectedsalary',
        'additionalinformation',
        'worklocation',
        'upload_file',
        'jurusan_bkk_id',
        'nim',
        'tahun_lulus',
        'ps_id',
        'pt_id'
    ];
    protected $loginNames = ['username', 'email'];
    protected $hidden = array('pivot');

    public function profile()
    {
        return $this->hasOne('\App\Model\UsersProfile', 'user_id', 'id');
    }
    
    public function bkk()
    {
        return $this->belongsTo('\App\Model\BkkModel', 'bkk_id');
    }

    public function lpk()
    {
        return $this->belongsTo('\App\Model\LpkModel', 'lpk_id');
    }

    public function perguruantinggi()
    {
        return $this->belongsTo('\App\Model\PerguruanTinggiModel', 'perguruan_tinggi_id');
    }

    public function pengajuanAK1()
    {
        return $this->hasOne('\App\Model\AK1Model', 'user_id', 'id');
    }
    // public function companiesprofile()
    // {
    //     return $this->hasOne('\App\Model\CompaniesProfile', 'user_id', 'id');
    // }

    //TODO MUST BE REPLACED
    //----------------------------
    public function companiesprofile()
    {
        return $this->hasOne('\App\Model\BkolPerusahaan', 'user_id', 'id');
    }

    public function datapencarikerja()
    {
        return $this->hasOne('\App\Model\BkolPencariKerja', 'user_id', 'id');
    }
    //----------------------------

    public function resume()
    {
        return $this->hasOne('\App\Model\ResumeUpload', 'user_id', 'id');
    }
    public function posts()
    {
        return $this->hasMany('\App\Model\BlogPosts', 'user_id', 'id');
    }
    public function jobapply()
    {
        return $this->hasMany('\App\Model\JobPostsApply', 'user_id', 'id');
    }

    public function pelatihandaftar()
    {
        return $this->hasMany('\App\Model\PelatihanDaftarPeserta', 'user_id', 'id');
    }

    public function riwayatpekerjaan()
    {
        return $this->hasMany('\App\Model\PekerjaanPosts', 'user_id', 'id');
    }
    public function riwayatpendidikan()
    {
        return $this->hasMany('\App\Model\PendidikanPosts', 'user_id', 'id');
    }
    public function riwayatnonformalpendidikan()
    {
        return $this->hasMany('\App\Model\PendidikanNonFormalPosts', 'user_id', 'id');
    }

    public function pendidikan()
    {
        return $this->hasMany('\App\Model\PendidikanPosts', 'user_id', 'id');
    }

    public function activation()
    {
        return $this->hasOne('\App\Model\ActivationModel', 'user_id', 'id');
    }
    public function oauth2()
    {
        return $this->hasMany('\App\Model\Oauth2Users', 'user_id', 'id');
    }

    public function pemerintah()
    {
        return $this->hasOne('\App\Model\PemerintahModel', 'user_id', 'id');
    }
    public function riwayatkompetensi()
    {
        return $this->hasMany('\App\Model\KompetensiPosts', 'user_id', 'id');
    }

    public function riwayatusaha()
    {
        return $this->hasMany('\App\Model\UsahaPosts', 'user_id', 'id');
    }

}
