<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        // List webhook logs or configurations
        // This is a placeholder for webhook management
        return DrivelinkHelper::respondJson('success', 'Webhook endpoint', ['message' => 'Webhook management endpoint']);
    }
}
