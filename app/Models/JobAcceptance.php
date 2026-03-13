<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAcceptance extends Model
{
    protected $fillable = [
        'job_post_id',
        'labor_id',
        'acceptor_id',
        'acceptor_type',
        'status'
    ];
}
