<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model{


    const SHOW = 1;
    const HIDE = 0;

    const NOT_DELETED = 0;
    const IS_DELETED = 1;
}
