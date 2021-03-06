<?php

namespace App\Http\Controllers;

use App\Http\Controllers\dataget\ListGetController;
use App\Http\Controllers\Message\StatusMessage;
use App\Http\Controllers\RoleManagement;
use App\MxpCompany;
use App\MxpCompanyUser;
use Auth;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller {

	public function __construct(Request $request) {

	}

	public function createUserForm(Request $request) {

		$roleManage = new RoleManagement();

		$group_id = $request->session()->get('group_id');

		if ($request->session()->get('user_type') == "company_user") {
			$companyList = MxpCompany::get()->where('id', Auth::user()->company_id);
		} else {
			$companyList = MxpCompany::get()->where('group_id', $group_id);
		}

		return view('user_management.create_user', compact('companyList'));

	}
	public function createUser(Request $request) {
		$roleManage = new RoleManagement();

		// Form InputValidation
		$validator = Validator::make($request->all(), [
			'personal_name' => 'required',
			'company_id' => 'required',
			'role_id' => 'required',
			'email' => 'required|email|unique:mxp_users',
			'password' => 'required|confirmed|min:6',
			'is_active' => 'required',
		]);
		if ($validator->fails()) {
			return redirect()->back()->withInput($request->input())->withErrors($validator->messages());
		}

		$group_id = $request->session()->get('group_id');

		$validationError = $validator->messages();
		$createUser = new MxpCompanyUser();
		$createUser->first_name = $request->personal_name;
		$createUser->type = "company_user";
		// $createUser->group_id     = Auth::user()->user_id;
		$createUser->group_id = $group_id;
		$createUser->company_id = $request->company_id;
		$createUser->email = $request->email;
		$createUser->password = bcrypt($request->password);
		$createUser->phone_no = $request->personal_phone_number;
		$createUser->is_active = $request->is_active;
		$createUser->user_role_id = $request->role_id;
		$createUser->save();

		StatusMessage::create('new_user_create', 'New User Created Successfully');

		// return view('user_management.create_user', compact('roleList'));
		return Redirect()->Route('create_user_view');
	}

	public function userList(Request $request) {

		$companyUser = ListGetController::companyUserList($request);

		return view('user_management.user_list', compact('companyUser'));

	}

	public function updateUserForm(Request $request) {
		$roleManage = new RoleManagement();
		$roleList = ListGetController::activeRoleList();

		$companyUser = ListGetController::companyUser($request, $request->id);

		$selectedUser = $companyUser;

		return view('user_management.update_user', compact('selectedUser', 'roleList'));
	}
	public function updateUser(Request $request) {
		if (!isset($request->password)) {
			$validator = Validator::make($request->all(), [
				'personal_name' => 'required',
				'email' => 'required|email',
				'personal_phone_number' => 'required',
				'is_active' => 'required',
			]);
		} else if (isset($request->password)) {
			$validator = Validator::make($request->all(), [
				'personal_name' => 'required',
				'email' => 'required|email',
				'password' => 'required|string|min:6',
				'personal_phone_number' => 'required',
				'is_active' => 'required',
			]);
		}

		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator->messages());
		}

		$user_update = MxpCompanyUser::find($request->user_id);
		$user_update->first_name = $request->personal_name;
		$user_update->phone_no = $request->personal_phone_number;
		$user_update->email = $request->email;
		if (isset($request->password)) {
			$user_update->password = bcrypt($request->password);
		}
		$user_update->is_active = $request->is_active;
		$user_update->user_role_id = $request->roleId;
		$user_update->save();

		return redirect()->Route('user_list_view');
	}
	public function deleteUser(Request $request) {

		$user_update = MxpCompanyUser::find($request->id);
		$user_update->delete();
		return redirect()->Route('user_list_view');
	}

}
