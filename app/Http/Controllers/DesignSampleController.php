<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DesignSampleController extends Controller
{
	
	public function index() {
		// $this->_helper->layout->setLayout('design-sample');
		return view('design-sample.index');
	}
}