<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ResumeUpload extends Model
{
    protected $table = 'b_resume_upload_cv';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'upload_document'
    ];
    public function userpencarikerja()
    {
        return $this->belongsTo('App\Model\Users', 'user_id', 'id');
    }
}
