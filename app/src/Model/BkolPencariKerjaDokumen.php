<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BkolPencariKerjaDokumen extends Model
{
    protected $table = 'b_pencaker_dokumen';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pencaker_id',
        'nama_file',
        'file_upload'
    ];
}
