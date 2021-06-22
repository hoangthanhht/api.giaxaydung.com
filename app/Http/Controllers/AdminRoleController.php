<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
class AdminRoleController extends Controller
{
    //
    private $role;
    public function __construct(Role $role)
    {
        $this->role = $role;
    }
    public function index () {
        $roles = $this->role->all();
    }
}
