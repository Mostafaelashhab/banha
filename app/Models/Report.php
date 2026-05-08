<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['reporter_id', 'target_type', 'target_id', 'reason', 'details', 'status'])]
class Report extends Model
{
    //
}
