<?php

$dbml = file_get_contents('dbdiagram.dbml');

$groups = [
    'part1' => [
        'orang', 'orang_status', 'organisasi', 'pengurus_organisasi', 'kab_kota', 'cabors', 'jenis', 'peran', 
        'events', 'riwayat_event', 'cabor_event', 'nomor_tanding', 'skala', 'sekolah', 'ekstrakurikuler_sekolah', 'jenis_ekstrakurikuler'
    ],
    'part2' => [
        'sarana', 'prasarana', 'fasilitas_prasarana', 'cabor_prasarana', 'foto_prasarana', 'kab_kota', 'cabors', 'jenis', 'lokasi'
    ],
    'part3' => [
        'users', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions', 
        'log_sistem', 'tickets', 'ticket_replies', 'pengumuman', 'informasi', 'cache', 'cache_locks', 'jobs', 
        'failed_jobs', 'job_batches', 'migrations', 'notifications', 'personal_access_tokens', 'password_reset_tokens', 'sessions'
    ]
];

// Extract all tables blocks
preg_match_all('/Table\s+(\w+)\s+\{.*?\}/s', $dbml, $tables, PREG_SET_ORDER);
// Extract all Ref blocks
preg_match_all('/Ref:\s+(\w+)\.\w+\s+>\s+(\w+)\.\w+/', $dbml, $refs, PREG_SET_ORDER);
preg_match_all('/Ref:\s+\w+\.\w+\s+>\s+\w+\.\w+/', $dbml, $allRefs);
$allRefsStr = $allRefs[0];

$output = "";

foreach ($groups as $groupName => $tableList) {
    $out = "// {$groupName}\n";
    foreach ($tables as $t) {
        $tableName = $t[1];
        if (in_array($tableName, $tableList)) {
            $out .= $t[0] . "\n\n";
        }
    }
    
    // Add valid refs where BOTH tables are in the group
    foreach ($refs as $idx => $r) {
        $t1 = $r[1];
        $t2 = $r[2];
        if (in_array($t1, $tableList) && in_array($t2, $tableList)) {
            $out .= $allRefsStr[$idx] . "\n";
        }
    }
    file_put_contents("{$groupName}.dbml", $out);
    $output .= "Generated {$groupName}\n";
}

echo $output;
