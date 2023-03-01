<?php
namespace Modules\Admin\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Hash;
use App\Models\Manager;
use App\Libraries\User\Admin as UserAdmin;
use Modules\Admin\Http\Form\Auth as AuthLogin;
use Library\Custom\Form;
use App\Http\Controllers\Controller;

class AuthController extends Controller{

    protected $guard;
 
    const BASE_URL = '/';

    protected $form;

    public function __construct(){
        $this->guard = Auth::guard('admin');
        $this->form = new AuthLogin();
    }

    public function index(Request $request){
        return redirect()->route('company.index');
    }

    public function login(Request $request){
        $this->form->setData($request->all());
        if ($request->isMethod('post')) {
            if ($this->form->isValid($request->all())) {
                $user = getUser();
                if ($user->login($this->form->getElement('login_id')->getValue(), $this->form->getElement('password')->getValue())) {
                    return redirect()->route('admin.company.index'); 
                }
                $message = $this->form->getElement('login_id')->getLabel() . 'または' . $this->form->getElement('password')->getLabel() . 'に誤りがあります';
                $this->form->getElement('password')->setMessages([$message]);
                $this->form->checkErrors();
            }
        }
        $this->form->getElement('password')->setValue('');
        return view('admin::auth.login')->with('form', $this->form);
    }
}