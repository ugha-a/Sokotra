<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/audit-logs', [
            'logs' => AdminAuditLog::query()
                ->with('user:id,name')
                ->latest('id')
                ->paginate(25),
        ]);
    }
}
