<?php
namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
class SeoAdviceController extends Controller {

    // public function __construct() {
    //     parent::__construct();

    //     // $this->_helper->layout->setLayout('seo-advice');

        // $this->_helper->layout->setLayout('seo-advice');
        // $this->view->headTitle('SEOアドバイス│ホームページ作成ツール');
    // }

    public function tdkCommon()
    {
        return view('seo-advice.tdk-common');
    }

    public function tdk()
    {
        return view('seo-advice.tdk');
    }

    public function contentCommon()
    {
        return view('seo-advice.content-common');
    }

    public function content()
    {
        return view('seo-advice.content');
    }
}
