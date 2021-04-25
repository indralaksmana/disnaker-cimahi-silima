<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LoggerActivityModel extends Model
{
    protected $connection = 'dblogger';
    protected $table = 'logger_activity';
    protected $primaryKey = 'id';
    protected $fillable = [
      'description',
      'userType',
      'userId',
      'route',
      'ipAddress',
      'userAgent',
      'locale',
      'referer',
      'methodType',
      'username'
    ];

}
