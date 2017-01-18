<?php

namespace App\Http\Controllers;

class DispatcherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
		
		public function call()
		{
			phpinfo();
		}

    //
}
