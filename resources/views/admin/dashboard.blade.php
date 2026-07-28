@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

{{-- ══ STAT CARDS ══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['label'=>'Total Insan Olahraga','value'=>$stats['total_orang'],'icon'=>'bi-people-fill','color'=>'#1a56db','bg'=>'#dbeafe','link'=>route('admin.orang')],
        ['label'=>'Atlet','value'=>$stats['total_atlet'],'icon'=>'bi-lightning-fill','color'=>'#f59e0b','bg'=>'#fef3c7','link'=>route('admin.orang')],
        ['label'=>'Pelatih','value'=>$stats['total_pelatih'],'icon'=>'bi-person-workspace','color'=>'#10b981','bg'=>'#d1fae5','link'=>route('admin.orang')],
        ['label'=>'Wasit/Juri','value'=>$stats['total_wasit'],'icon'=>'bi-person-badge-fill','color'=>'#8b5cf6','bg'=>'#ede9fe','link'=>route('admin.orang')],
        ['label'=>'Cabang Olahraga','value'=>$stats['total_cabor'],'icon'=>'bi-dribbble','color'=>'#06b6d4','bg'=>'#cffafe','link'=>route('admin.master.cabor')],
        ['label'=>'Manajemen Event','value'=>$stats['total_event'],'icon'=>'bi-calendar-event-fill','color'=>'#ec4899','bg'=>'#fce7f3','link'=>route('admin.events')],
        ['label'=>'Prasarana','value'=>$stats['total_prasarana'],'icon'=>'bi-geo-alt-fill','color'=>'#ef4444','bg'=>'#fee2e2','link'=>route('admin.prasarana')],
        ['label'=>'Organisasi','value'=>$stats['total_organisasi'],'icon'=>'bi-building','color'=>'#64748b','bg'=>'#f1f5f9','link'=>route('admin.organisasi')],
    ];
    @endphp

    @foreach($cards as $c)
    <div class="col-xl-3 col-md-4 col-sm-6">
        <a href="{{ $c['link'] }}" class="text-decoration-none">
            <div class="stat-card" style="transition:.2s;cursor:pointer" onmouseenter="this.style.transform='translateY(-3px)'" onmouseleave="this.style.transform=''">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:{{ $c['bg'] }};color:{{ $c['color'] }}">
                        <i class="bi {{ $c['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="stat-value counter" data-target="{{ $c['value'] }}" style="color:{{ $c['color'] }}">0</div>
                        <div class="stat-label">{{ $c['label'] }}</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ══ CHARTS ROW ══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Top 10 Cabor by Atlet --}}
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="card-header">
                <span><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Top 10 Cabor — Jumlah Atlet</span>
                <a href="{{ route('admin.master.cabor') }}" class="small text-primary text-decoration-none">Lihat Semua →</a>
            </div>
            <div class="card-body" style="height:280px;position:relative">
                <canvas id="chartCabor"></canvas>
            </div>
        </div>
    </div>

    {{-- Gender Doughnut --}}
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="card-header">
                <span><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Distribusi Gender</span>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="height:280px">
                <canvas id="chartGender" style="max-width:200px;max-height:200px"></canvas>
                <div class="d-flex gap-4 mt-3">
                    <div class="text-center">
                        <div class="fw-bold text-primary fs-5">{{ number_format($genderData['L'] ?? 0) }}</div>
                        <small class="text-muted">Laki-laki</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold text-danger fs-5">{{ number_format($genderData['P'] ?? 0) }}</div>
                        <small class="text-muted">Perempuan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ CHARTS ROW 2 ════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- SDM per Kab/Kota --}}
    @if(auth()->user() && auth()->user()->hasRole('SuperAdmin'))
    <div class="col-lg-7">
        <div class="admin-card h-100">
            <div class="card-header">
                <span><i class="bi bi-map-fill me-2 text-primary"></i>Distribusi Orang per Kab/Kota (Top 15)</span>
            </div>
            <div class="card-body" style="height:300px;position:relative">
                <canvas id="chartKabKota"></canvas>
            </div>
        </div>
    </div>

    {{-- SDM per Jenis --}}
    <div class="col-lg-5">
        <div class="admin-card h-100">
            <div class="card-header">
                <span><i class="bi bi-layers-fill me-2 text-primary"></i>Rekapitulasi per Jenis</span>
            </div>
            <div class="card-body">
                @foreach($perJenis as $j)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-medium small">{{ $j['nama'] }}</div>
                        <div class="progress mt-1" style="height:6px;width:200px;border-radius:4px">
                            @php $pct = $stats['total_orang'] > 0 ? ($j['total'] / $stats['total_orang']) * 100 : 0; @endphp
                            <div class="progress-bar bg-primary" style="width:{{ $pct }}%;border-radius:4px"></div>
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 fw-bold">{{ number_format($j['total']) }}</span>
                </div>
                @endforeach

                {{-- Event summary --}}
                <div class="mt-4 pt-3 border-top">
                    <div class="fw-semibold small text-muted mb-2">EVENT TERDAFTAR</div>
                    @foreach($eventTerbaru as $ev)
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="small text-truncate me-2" style="max-width:200px">
                            <i class="bi bi-calendar2-check me-1 text-primary"></i>{{ $ev->nama }}
                        </div>
                        <span class="badge bg-{{ $ev->status==='aktif'?'success':'secondary' }} bg-opacity-10 text-{{ $ev->status==='aktif'?'success':'secondary' }} text-nowrap">
                            {{ $ev->status }}
                        </span>
                    </div>
                    @endforeach
                    <a href="{{ route('admin.events') }}" class="btn btn-sm btn-outline-primary mt-2 w-100 rounded-pill">
                        <i class="bi bi-calendar-event me-1"></i>Kelola Event
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ══ LOG + PENGUMUMAN ════════════════════════════════════════ --}}
<div class="row g-3">

    {{-- Log Aktivitas Terakhir --}}
    @if(auth()->user() && auth()->user()->hasRole('SuperAdmin'))
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="card-header">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Terakhir</span>
                <a href="{{ route('admin.log-sistem') }}" class="small text-primary text-decoration-none">Lihat Semua →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Waktu</th>
                                <th>Aksi</th>
                                <th>Modul</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logTerbaru as $log)
                            <tr>
                                <td class="ps-3 text-muted small text-nowrap">{{ $log->created_at->format('d/m H:i') }}</td>
                                <td>
                                    @php $colors = ['CREATE'=>'success','UPDATE'=>'warning','DELETE'=>'danger','IMPORT'=>'info']; @endphp
                                    <span class="badge bg-{{ $colors[$log->action] ?? 'secondary' }} bg-opacity-10 text-{{ $colors[$log->action] ?? 'secondary' }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="small">{{ $log->module }}</td>
                                <td class="small text-muted text-truncate" style="max-width:180px">{{ $log->description }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Pengumuman Aktif --}}
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="card-header">
                <span><i class="bi bi-megaphone-fill me-2 text-warning"></i>Pengumuman Aktif</span>
                <a href="{{ route('admin.pengumuman') }}" class="small text-primary text-decoration-none">Kelola →</a>
            </div>
            <div class="card-body p-0">
                @forelse($pengumuman as $p)
                <div class="d-flex align-items-start gap-3 p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="pengumuman-icon {{ $p->is_pinned ? 'pinned' : '' }}" style="flex-shrink:0">
                        <i class="bi {{ $p->is_pinned ? 'bi-pin-fill' : 'bi-bell-fill' }}"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 small">{{ $p->judul }}</h6>
                        <p class="text-muted mb-0" style="font-size:.78rem">{{ Str::limit($p->isi, 90) }}</p>
                        <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $p->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-megaphone opacity-25 d-block mb-2" style="font-size:2.5rem"></i>
                    Belum ada pengumuman aktif
                </div>
                @endforelse

                <div class="p-3 border-top">
                    <a href="{{ route('admin.pengumuman') }}" class="btn btn-sm btn-outline-warning rounded-pill w-100">
                        <i class="bi bi-plus me-1"></i>Buat Pengumuman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Counter Animation ─────────────────────────────────
    document.querySelectorAll('.counter').forEach(el => {
        const target = parseInt(el.dataset.target);
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 60));
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString('id-ID');
            if (current >= target) clearInterval(timer);
        }, 16);
    });

    // ── Chart: Top 10 Cabor by Atlet ─────────────────────
    const caborData = @json($topCabor);
    if (caborData.length && document.getElementById('chartCabor')) {
        new Chart(document.getElementById('chartCabor'), {
            type: 'bar',
            data: {
                labels: caborData.map(d => d.nama.length > 18 ? d.nama.substring(0,18)+'…' : d.nama),
                datasets: [{
                    label: 'Jumlah Atlet',
                    data: caborData.map(d => d.total),
                    backgroundColor: [
                        '#3b82f6','#f59e0b','#10b981','#8b5cf6','#ef4444',
                        '#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'
                    ],
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y.toLocaleString('id-ID')} atlet` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11 }, callback: v => v.toLocaleString('id-ID') }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, maxRotation: 35, minRotation: 20 }
                    }
                }
            }
        });
    }

    // ── Chart: Gender Doughnut ────────────────────────────
    const gL = {{ $genderData['L'] ?? 0 }};
    const gP = {{ $genderData['P'] ?? 0 }};
    if (document.getElementById('chartGender')) {
        new Chart(document.getElementById('chartGender'), {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [gL, gP],
                    backgroundColor: ['rgba(26,86,219,0.85)', 'rgba(236,72,153,0.85)'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('id-ID')} orang`
                        }
                    }
                }
            }
        });
    }

    // ── Chart: Distribusi per Kab/Kota ───────────────────
    const kabData = @json($perKabKota);
    if (kabData.length && document.getElementById('chartKabKota')) {
        new Chart(document.getElementById('chartKabKota'), {
            type: 'bar',
            data: {
                labels: kabData.map(d => d.nama.replace('Kabupaten ', 'Kab. ').replace('Kota ', 'Kota ')),
                datasets: [{
                    label: 'Jumlah Orang',
                    data: kabData.map(d => d.total),
                    backgroundColor: 'rgba(26,86,219,0.75)',
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x.toLocaleString('id-ID')} orang` } }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, callback: v => v.toLocaleString('id-ID') }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
