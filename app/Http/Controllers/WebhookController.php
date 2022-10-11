<?php

namespace App\Http\Controllers;

use App\Jobs\WebhookJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        WebhookJob::dispatch($request->data)->onQueue('admin');
        return response(status: 201);
    }
}