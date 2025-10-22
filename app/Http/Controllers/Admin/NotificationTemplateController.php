<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationTemplateService;
use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationTemplateController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationTemplateService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display template management dashboard
     */
    public function index(Request $request)
    {
        // Check permissions
        if (!auth('admin')->user()->hasPermission('manage_notifications')) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        $type = $request->get('type', 'email');
        $templates = $this->notificationService->getTemplates($type);

        // Get statistics
        $stats = $this->notificationService->getNotificationStats();

        return view('admin.notifications.index', compact('templates', 'type', 'stats'));
    }

    /**
     * Show create template form
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'email');
        return view('admin.notifications.create', compact('type'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,sms',
            'name' => 'required|string|max:255|unique:' . ($request->type === 'email' ? 'email_templates' : 'sms_templates') . ',name',
            'subject' => 'required_if:type,email|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|json',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'subject', 'body', 'variables', 'is_active']);
        $data['variables'] = json_decode($data['variables'] ?? '[]', true);
        $data['is_active'] = $request->has('is_active');

        $result = $this->notificationService->saveTemplate($request->type, $data);

        if ($result['success']) {
            return redirect()->route('admin.notifications.index', ['type' => $request->type])
                ->with('success', 'Template created successfully');
        }

        return redirect()->back()->with('error', $result['error'])->withInput();
    }

    /**
     * Show edit template form
     */
    public function edit($type, $id)
    {
        if ($type === 'email') {
            $template = EmailTemplate::findOrFail($id);
        } elseif ($type === 'sms') {
            $template = SmsTemplate::findOrFail($id);
        } else {
            abort(404);
        }

        return view('admin.notifications.edit', compact('template', 'type'));
    }

    /**
     * Update template
     */
    public function update(Request $request, $type, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:' . ($type === 'email' ? 'email_templates' : 'sms_templates') . ',name,' . $id,
            'subject' => 'required_if:type,email|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|json',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['name', 'subject', 'body', 'variables', 'is_active']);
        $data['id'] = $id;
        $data['variables'] = json_decode($data['variables'] ?? '[]', true);
        $data['is_active'] = $request->has('is_active');

        $result = $this->notificationService->saveTemplate($type, $data);

        if ($result['success']) {
            return redirect()->route('admin.notifications.index', ['type' => $type])
                ->with('success', 'Template updated successfully');
        }

        return redirect()->back()->with('error', $result['error'])->withInput();
    }

    /**
     * Delete template
     */
    public function destroy($type, $id)
    {
        $result = $this->notificationService->deleteTemplate($type, $id);

        if ($result['success']) {
            return redirect()->route('admin.notifications.index', ['type' => $type])
                ->with('success', 'Template deleted successfully');
        }

        return redirect()->back()->with('error', $result['error']);
    }

    /**
     * Preview template
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,sms',
            'template_name' => 'required|string',
            'variables' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $variables = json_decode($request->variables ?? '{}', true);

        $result = $this->notificationService->previewTemplate(
            $request->type,
            $request->template_name,
            $variables
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json(['error' => $result['error']], 404);
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,sms',
            'template_name' => 'required|string',
            'recipient' => 'required',
            'variables' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $variables = json_decode($request->variables ?? '{}', true);
        $options = ['recipient' => $request->recipient];

        $result = $this->notificationService->sendNotification(
            $request->type,
            $request->template_name,
            $request->recipient,
            $variables,
            $options
        );

        if ($result['success']) {
            return response()->json(['message' => 'Test notification sent successfully']);
        }

        return response()->json(['error' => $result['error']], 500);
    }

    /**
     * Get notification statistics
     */
    public function stats(Request $request)
    {
        $dateRange = [];
        if ($request->has(['start_date', 'end_date'])) {
            $dateRange = [
                $request->start_date,
                $request->end_date
            ];
        }

        $stats = $this->notificationService->getNotificationStats($dateRange);

        return response()->json($stats);
    }
}
