<?php

namespace App\Helpers;

use App\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log($event, $module, $details = null)
    {
        $business_id = auth()->user()->business_id ?? null;

        return \App\AuditLog::create([
            'log_name'     => 'default',
            'description'  => $event,            
            'subject_type' => $module,            
            'event'        => $event,             
            'causer_id'    => auth()->id(),       
            'causer_type'  => 'App\User',      
            'properties'   => $details,          
            'business_id'  => $business_id,  
        ]);
    }
}