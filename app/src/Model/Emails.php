<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{
    protected $table = 'emails';
    protected $primaryKey = 'id';
    protected $fillable = [
        'secure_id',
        'template_id',
        'send_to',
        'subject',
        'html',
        'plain_text'
    ];
}
