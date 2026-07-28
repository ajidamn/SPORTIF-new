<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketRepliedNotification;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('SuperAdmin')) {
            $tickets = Ticket::with('user')->orderBy('created_at', 'desc')->get();
        } else {
            $tickets = Ticket::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        }

        return view('admin.aduan.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('tickets', 'public');
        }

        $ticket = Ticket::create([
            'kode_tiket' => 'TKT-' . date('ymd') . '-' . rand(1000, 9999),
            'user_id' => auth()->id(),
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'status' => 'open',
            'lampiran' => $lampiranPath,
        ]);

        // Send Notification to all SuperAdmins
        $superAdmins = User::role('SuperAdmin')->get();
        Notification::send($superAdmins, new TicketCreatedNotification($ticket));

        return redirect()->route('admin.aduan.show', $ticket->id)->with('success', 'Tiket aduan berhasil dibuat.');
    }

    public function show($id)
    {
        $ticket = Ticket::with(['user', 'replies.user'])->findOrFail($id);
        
        // Cek privasi
        $user = auth()->user();
        if (!$user->hasRole('SuperAdmin') && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        return view('admin.aduan.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Cek privasi
        $user = auth()->user();
        if (!$user->hasRole('SuperAdmin') && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        $request->validate([
            'pesan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('tickets', 'public');
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'pesan' => $request->pesan,
            'lampiran' => $lampiranPath,
        ]);

        // Auto update status to in_progress if replied by SuperAdmin and it was open
        if ($user->hasRole('SuperAdmin') && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Send Notification
        if ($user->hasRole('SuperAdmin')) {
            // Notify Ticket Owner
            $ticket->user->notify(new TicketRepliedNotification($ticket, $user->name));
        } else {
            // Notify SuperAdmins
            $superAdmins = User::role('SuperAdmin')->get();
            Notification::send($superAdmins, new TicketRepliedNotification($ticket, $user->name));
        }

        return redirect()->back()->with('success', 'Pesan balasan berhasil dikirim.');
    }

    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Cek privasi
        $user = auth()->user();
        if (!$user->hasRole('SuperAdmin') && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        $ticket->update(['status' => 'closed']);

        return redirect()->back()->with('success', 'Tiket berhasil ditutup.');
    }

    public function fetchMessages($id, Request $request)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Cek privasi
        $user = auth()->user();
        if (!$user->hasRole('SuperAdmin') && $ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastId = $request->query('last_id', 0);
        
        $replies = \App\Models\TicketReply::with('user.roles')
            ->where('ticket_id', $ticket->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($reply) use ($user) {
                return [
                    'id' => $reply->id,
                    'pesan' => $reply->pesan,
                    'lampiran' => $reply->lampiran ? asset('storage/' . $reply->lampiran) : null,
                    'created_at' => $reply->created_at->format('d M, H:i'),
                    'user_name' => $reply->user->name ?? 'Unknown',
                    'user_initial' => substr($reply->user->name ?? 'U', 0, 1),
                    'is_superadmin' => $reply->user->hasRole('SuperAdmin'),
                    'is_own' => $reply->user_id === $user->id
                ];
            });

        return response()->json([
            'status' => $ticket->status,
            'replies' => $replies
        ]);
    }
}
