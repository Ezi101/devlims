<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppSetting extends Model
{
    use HasFactory;
    protected $table = 'whatsapp_settings';
    protected $fillable = ['instance_id', 'access_token', 'recipients', 'modules', 'department', 'app_key', 'auth_key'];
}
