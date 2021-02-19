<?php
namespace search\model;

use Illuminate\Database\Eloquent\Model as model;

class Users extends model
{
    public $incrementing  = false;
    protected $table      = 'users';
    protected $primaryKey = 'mail';
    private $users;

}
