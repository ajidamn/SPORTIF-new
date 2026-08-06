<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventPesertaController extends Controller
{
    public function index($id)
    {
        $event = Event::with(['jenis', 'skala', 'cabors'])->findOrFail($id);
        
        $user = auth()->user();
        $roles = $user->getRoleNames();
        $isReadOnly = $roles->contains(fn($r) => str_starts_with($r, 'Kepala') || str_starts_with($r, 'Ketua'));
        if (!$event->isEditableBy($user)) {
            $isReadOnly = true;
        }
        return view('admin.event-peserta', [
            'title' => 'Peserta & Medali - ' . $event->nama,
            'pageSlug' => 'events.peserta',
            'event' => $event,
            'isReadOnly' => $isReadOnly
        ]);
    }
}
