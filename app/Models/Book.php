<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model{

    protected $table = 'book';

    protected $primaryKey = 'id';

    protected $appends = ['status_text'];

    const STATUS_SERIALIZE = 1;
    const STATUS_FINISH = 2;
    const STATUS_BREAK_WEEK = 3;
    const STATUS_BREAK_MONTH = 4;
    const ENABLE_TRUE = 1;
    const ENABLE_FALSE = 2;

    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_FINISH :
                $text = '完结';
                break;
            case self::STATUS_BREAK_WEEK :
                $text = '断更一周';
                break;
            case self::STATUS_BREAK_MONTH :
                $text = '断更一月';
                break;
            case self::STATUS_SERIALIZE :
            default:
                $text = '连载';
                break;
        }
        return $text;
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'cid', 'cid')->select('cid', 'cat_name', 'channel_id');
    }
}
