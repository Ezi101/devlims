<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class STRRemarks extends Model
{
    use HasFactory;

    protected $table = 's_t_r_remarks';
    protected $guarded = ['id'];


    /**
     * Get the remark_to that owns the STRRemarks
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remarkTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remark_to', 'id');
    }

    
    /**
     * Get the remark_by that owns the STRRemarks
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remarkBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remark_by', 'id');
    }
    
}
