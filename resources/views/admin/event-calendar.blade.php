@extends('layouts.admin')
@section('title', 'Kalender Event')

@section('content')
<div class="admin-card">
    <div class="card-header">
        <span><i class="bi bi-calendar-event me-2 text-primary"></i>Kalender Event</span>
    </div>
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- FullCalendar JS/CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        events: function(info, successCallback, failureCallback) {
            fetch(`/api/v1/events?tanggal_mulai_from=${info.startStr}&tanggal_mulai_to=${info.endStr}&per_page=500`, {
                headers: { 
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })
            .then(r => r.json())
            .then(data => {
                const events = (data.data || []).map(e => ({
                    id: e.id,
                    title: (e.approval_status === 'pending' ? '⏳ ' : '') + e.nama,
                    start: e.tanggal_mulai,
                    end: e.tanggal_selesai ? (new Date(new Date(e.tanggal_selesai).getTime() + 86400000).toISOString().split('T')[0]) : null, // exclusive end
                    color: getEventColor(e),
                    extendedProps: e
                }));
                successCallback(events);
            })
            .catch(failureCallback);
        },
        eventClick: function(info) {
            window.location.href = `/admin/events/${info.event.id}/peserta`;
        }
    });
    calendar.render();
});

function getEventColor(event) {
    if (event.approval_status === 'pending') return '#ffc107'; // warning
    if (event.status === 'dibatalkan') return '#6c757d'; // gray

    const skala = event.skala?.nama?.toLowerCase() || '';
    if (skala.includes('internasional')) return '#dc3545'; // danger
    if (skala.includes('nasional')) return '#fd7e14'; // orange
    if (skala.includes('provinsi')) return '#0d6efd'; // primary
    return '#198754'; // Daerah (success)
}
</script>

<style>
#calendar {
    max-width: 100%;
    margin: 0 auto;
    font-family: inherit;
}
.fc-event {
    cursor: pointer;
}
</style>
@endsection
