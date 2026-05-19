<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_request_id', 'business_id', 'quoted_price', 'note', 'contacted_at',
    ];

    protected $casts = [
        'quoted_price' => 'integer',
        'contacted_at' => 'datetime',
    ];

    public function jobRequest(): BelongsTo { return $this->belongsTo(JobRequest::class); }
    public function business(): BelongsTo   { return $this->belongsTo(Business::class); }
}
