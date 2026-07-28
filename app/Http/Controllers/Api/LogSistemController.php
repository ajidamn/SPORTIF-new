<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use Illuminate\Http\Request;

class LogSistemController extends Controller
{
    public function index(Request $r)
    {
        $q = LogSistem::with('user')->latest();
        if ($r->module) $q->where('module', $r->module);
        if ($r->action) $q->where('action', $r->action);
        if ($r->user) {
            $q->where(function($query) use ($r) {
                $query->whereHas('user', function($sub) use ($r) {
                    $sub->where('name', 'like', "%{$r->user}%")->orWhere('username', 'like', "%{$r->user}%");
                })->orWhere('user_name', 'like', "%{$r->user}%");
            });
        }
        if ($r->date_from) $q->whereDate('created_at', '>=', $r->date_from);
        if ($r->date_to) $q->whereDate('created_at', '<=', $r->date_to);
        if ($r->search) $q->where('description', 'like', "%{$r->search}%");
        return response()->json($q->paginate($r->per_page ?? 25));
    }
}
