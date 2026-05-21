<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Inbox extends Model
{
    use HasFactory;
    protected $fillable = ['message','message_to','message_from','business_id'];

    /**
     * Get the remark_to that owns the STRRemarks
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remarkTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'message_to', 'id');
    }

    
    /**
     * Get the remark_by that owns the STRRemarks
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remarkBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'message_from', 'id');
    }
}
