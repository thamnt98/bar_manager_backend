<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 12/27/19
 * Time: 6:03 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class RegisterMail extends Model
{
    use Notifiable;
    protected $fillable = [
        'email', 'generated_code'
    ];
}