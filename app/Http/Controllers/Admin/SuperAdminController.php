<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function index()
    {
        return view('admin.superadmin.index');
    }

    /**
     * Show user management placeholder
     */
    public function users()
    {
        // Return a simple HTML snippet so tests can assert visible text
        return response('<h1>User Management</h1><p>Manage admin users here.</p>');
    }

    /**
     * Show audit logs placeholder
     */
    public function auditLogs()
    {
        return response('<h1>Audit Logs</h1><p>Audit entries will be shown here.</p>');
    }

    /**
     * Show settings placeholder
     */
    public function settings()
    {
        return response('<h1>System Settings</h1><p>Configure system settings here.</p>');
    }
}
