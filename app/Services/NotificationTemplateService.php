<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendSmsNotification;

class NotificationTemplateService
{
    /**
     * Send notification using template
     */
    public function sendNotification(string $type, string $templateName, string $recipient, array $variables = [], array $options = []): array
    {
        try {
            $template = $this->getTemplate($type, $templateName);

            if (!$template || !$template->is_active) {
                return [
                    'success' => false,
                    'error' => "Template '{$templateName}' not found or inactive"
                ];
            }

            // Replace variables in content
            $content = $this->replaceVariables($template, $variables);

            // Log the notification attempt
            $log = NotificationLog::create([
                'type' => $type,
                'recipient' => $recipient,
                'template_id' => $template->id,
                'template_name' => $templateName,
                'variables' => $variables,
                'status' => 'pending',
                'created_by' => auth('admin')->id() ?? 1
            ]);

            // Send via queued job
            if ($type === 'email') {
                SendEmailNotification::dispatch($log->id, $content, $options);
            } elseif ($type === 'sms') {
                SendSmsNotification::dispatch($log->id, $content, $options);
            }

            return [
                'success' => true,
                'log_id' => $log->id,
                'message' => 'Notification queued for sending'
            ];

        } catch (\Exception $e) {
            Log::error("Failed to send {$type} notification: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Preview template with variables
     */
    public function previewTemplate(string $type, string $templateName, array $variables = []): array
    {
        try {
            $template = $this->getTemplate($type, $templateName);

            if (!$template) {
                return [
                    'success' => false,
                    'error' => "Template '{$templateName}' not found"
                ];
            }

            $content = $this->replaceVariables($template, $variables);

            return [
                'success' => true,
                'template' => $template,
                'content' => $content,
                'variables' => $variables
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log notification delivery status
     */
    public function logNotification(int $logId, string $status, array $details = []): void
    {
        $log = NotificationLog::find($logId);

        if (!$log) {
            Log::warning("Notification log {$logId} not found");
            return;
        }

        $updateData = ['status' => $status];

        if ($status === 'sent') {
            $updateData['sent_at'] = now();
        } elseif ($status === 'delivered') {
            $updateData['delivered_at'] = now();
        } elseif ($status === 'failed') {
            $updateData['error_message'] = $details['error'] ?? 'Unknown error';
        }

        $log->update($updateData);

        Log::info("Notification {$logId} status updated to {$status}");
    }

    /**
     * Get template by type and name
     */
    protected function getTemplate(string $type, string $name)
    {
        if ($type === 'email') {
            return EmailTemplate::where('name', $name)->first();
        } elseif ($type === 'sms') {
            return SmsTemplate::where('name', $name)->first();
        }

        return null;
    }

    /**
     * Replace variables in template content
     */
    protected function replaceVariables($template, array $variables): array
    {
        $content = [];

        if ($template instanceof EmailTemplate) {
            $content['subject'] = $this->replacePlaceholders($template->subject, $variables);
            $content['body'] = $this->replacePlaceholders($template->body, $variables);
        } elseif ($template instanceof SmsTemplate) {
            $content['body'] = $this->replacePlaceholders($template->body, $variables);
        }

        return $content;
    }

    /**
     * Replace placeholders in text
     */
    protected function replacePlaceholders(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $placeholder = "{{$key}}";
            $text = str_replace($placeholder, $value, $text);
        }

        return $text;
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(array $dateRange = []): array
    {
        $query = NotificationLog::query();

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', $dateRange);
        }

        $stats = $query->selectRaw('
            type,
            status,
            COUNT(*) as count,
            AVG(TIMESTAMPDIFF(SECOND, created_at, sent_at)) as avg_send_time,
            AVG(TIMESTAMPDIFF(SECOND, sent_at, delivered_at)) as avg_delivery_time
        ')
        ->groupBy('type', 'status')
        ->get();

        return [
            'total_sent' => $stats->whereIn('status', ['sent', 'delivered'])->sum('count'),
            'total_failed' => $stats->where('status', 'failed')->sum('count'),
            'by_type' => $stats->groupBy('type'),
            'by_status' => $stats->groupBy('status')
        ];
    }

    /**
     * Create or update template
     */
    public function saveTemplate(string $type, array $data): array
    {
        try {
            $data['updated_by'] = auth('admin')->id() ?? 1;

            if ($type === 'email') {
                if (isset($data['id'])) {
                    $template = EmailTemplate::find($data['id']);
                    $template->update($data);
                } else {
                    $data['created_by'] = $data['updated_by'];
                    $template = EmailTemplate::create($data);
                }
            } elseif ($type === 'sms') {
                if (isset($data['id'])) {
                    $template = SmsTemplate::find($data['id']);
                    $template->update($data);
                } else {
                    $data['created_by'] = $data['updated_by'];
                    $template = SmsTemplate::create($data);
                }
            }

            return [
                'success' => true,
                'template' => $template
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate(string $type, int $id): array
    {
        try {
            if ($type === 'email') {
                EmailTemplate::findOrFail($id)->delete();
            } elseif ($type === 'sms') {
                SmsTemplate::findOrFail($id)->delete();
            }

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all templates
     */
    public function getTemplates(string $type = null): array
    {
        $templates = [];

        if ($type === 'email' || $type === null) {
            $templates['email'] = EmailTemplate::with('creator')->get();
        }

        if ($type === 'sms' || $type === null) {
            $templates['sms'] = SmsTemplate::with('creator')->get();
        }

        return $templates;
    }
}
