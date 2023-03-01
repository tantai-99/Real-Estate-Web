<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Library\Custom\User\Admin;

class IndexController extends Controller
{

    public function index()
    {

		$user = Admin::getInstance()->getProfile();
		if (!$user) {
			return redirect('/admin/auth/login?r=1');
		}
		if($user->privilege_edit_flg == "1") {
	    	return redirect()->route("admin.company.index");
		}else if($user->privilege_manage_flg == "1") {
			return redirect()->route("admin.acount.index");
		}else{
			return redirect()->route("admin.password.index");
		}

		return;
    }
}
