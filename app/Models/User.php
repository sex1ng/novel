<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model{

    protected $table = 'user';

    protected $primaryKey = 'id';
    /**
     * @var mixed
     */
    private $uid;
    /**
     * @var array|mixed|string|null
     */
    private $androidId;
    /**
     * @var mixed|string
     */
    private $nickname;
    /**
     * @var mixed|string
     */
    private $avatar;
    /**
     * @var int|mixed
     */
    private $is_login;

}
