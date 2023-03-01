<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Traits\MySoftDeletes;
use Library\Custom\Crypt\Password;

class CompanyAccount extends Model implements AuthenticatableContract, CanResetPasswordContract{
    use Authenticatable, CanResetPassword;
    use MySoftDeletes;

    protected $table = 'company_account';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    protected $_cryptMap = array();
 
    protected $fillable = [
        'company_id',
        'login_id',
        'password',
        'api_key',
        'login_failed_count',
        'locked_date',
        'login_date',

    ];

    protected $guarded = [
        'id'
    ];


    public function __construct() {
        parent::__construct();
        $this->_cryptMap['password'] = new Password();
    }
    
    public function getAuthIdentifierName() {
        return 'company_id';
    }

    public function company() {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function secondEstate() {
        return $this->hasOne(SecondEstate::class,'company_id','company_id');
    }
}
