<?php

namespace App\Http\Controllers\SalesAgent\Status;

use App\Http\Controllers\Message\ActionMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use DB;

class InactiveController extends Controller
{
    public function viewInactive(){
    	return view('sales_agent.status.inactive.inactiveList');
    }
}
