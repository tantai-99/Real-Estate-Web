<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Traits\MySoftDeletes;
use Library\Custom\Crypt\Password;
use Exception;

class Manager extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    use MySoftDeletes;

    protected $table = 'manager';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'name',
        'login_id',
        'password',
        'privilege_edit_flg',
        'privilege_manage_flg',
        'privilege_create_flg',
        'privilege_open_flg',
        'staff_flg',
        'staff_id',
        'login_failed_count',
        'locked_date',
        'creator_login_failed_count',
        'creator_locked_date',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date'
    ];

    protected $guarded = [
        'id'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->_cryptMap['password'] = new Password();
    }

    // Check Privilege Edit
    public function checkPrivilegeEdit()
    {
        if ($this->privilege_edit_flg == 1) {
            return true;
        }
        return false;
    }

    public function getDataForId($id)
    {
        if (!preg_match("/^[0-9 ]{1,}$/", $id)) {
            throw new Exception("管理者IDに数字以外が指定されています");
        }

        // $select = $this->select();
        // $select->where("id = ?", $id);
        // return $this->fetchRow($select);
        return $this->where("id", $id)->first();
    }
}
