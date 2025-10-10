<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = collect([]);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        return back()->with('info', 'Notification functionality coming soon!');
    }

    public function show($id)
    {
        return view('admin.notifications.show');
    }

    public function edit($id)
    {
        return view('admin.notifications.edit');
    }

    public function update(Request $request, $id)
    {
        return back()->with('info', 'Notification functionality coming soon!');
    }

    public function destroy($id)
    {
        return back()->with('info', 'Notification functionality coming soon!');
    }

    public function compose()
    {
        return view('admin.notifications.compose');
    }

    public function sendBulk(Request $request)
    {
        return back()->with('info', 'Bulk notification functionality coming soon!');
    }

    public function sendIndividual(Request $request)
    {
        return back()->with('info', 'Individual notification functionality coming soon!');
    }

    public function getTemplates()
    {
        return response()->json(['templates' => []]);
    }

    public function saveTemplate(Request $request)
    {
        return back()->with('info', 'Template functionality coming soon!');
    }

    public function deleteTemplate($template)
    {
        return back()->with('info', 'Template functionality coming soon!');
    }

    public function history()
    {
        return view('admin.notifications.history');
    }

    public function deliveryStats()
    {
        return response()->json(['stats' => []]);
    }
}