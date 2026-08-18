<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait TenancyScope
{
    /**
     * Boot the tenancy scope trait for a model.
     *
     * @return void
     */
    protected static function bootTenancyScope()
    {
        static::addGlobalScope('tenancy', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Skip filtering for SuperAdmin
                if ($user->hasRole('SuperAdmin')) {
                    return;
                }

                $table = $builder->getModel()->getTable();

                // Khusus untuk tabel events, kita gunakan logika visibility yang lebih kompleks (R1-R8)
                if ($table === 'events') {
                    // R5: Filter by jenis_id
                    if ($user->jenis_id) {
                        $builder->where('events.jenis_id', $user->jenis_id);
                    }
                
                    // R2, R3, R6, R7: Filter by kab_kota + skala
                    if ($user->kab_kota_id) {
                        // User kab/kota: lihat event kab-nya ATAU event skala >= Provinsi
                        $builder->where(function($q) use ($user) {
                            $q->where('events.kab_kota_id', $user->kab_kota_id)
                              ->orWhereHas('skala', function($sq) {
                                  $sq->where('nama', '!=', 'Daerah');
                              });
                        });
                    }
                    // User provinsi (tanpa kab_kota_id) -> R6: lihat semua
                
                    // R4, R8: Filter by cabor
                    if ($user->cabor_id) {
                        $builder->whereHas('cabors', function($q) use ($user) {
                            $q->where('cabor_id', $user->cabor_id);
                        });
                    }
                    // User tanpa cabor_id = multi-viewer -> tidak filter cabor
                
                    // NPCI scope
                    $isNPCI = $user->hasRole('Ketua NPCI Provinsi')
                        || $user->hasRole('Ketua NPCI Kab/Kota')
                        || $user->hasRole('Admin NPCI Provinsi');
                    if ($isNPCI) {
                        $builder->where('events.disabilitas', true);
                    }
                
                    return; // Skip filter generik
                }

                // 1. Filter by kab_kota_id (or lokasi_id) if user has kab_kota_id
                if ($user->kab_kota_id) {
                    if (Schema::hasColumn($table, 'kab_kota_id')) {
                        $builder->where($table . '.kab_kota_id', $user->kab_kota_id);
                    } elseif (Schema::hasColumn($table, 'lokasi_id')) {
                        // For Prasarana, it uses lokasi_id instead of kab_kota_id
                        $builder->where($table . '.lokasi_id', $user->kab_kota_id);
                    }
                }

                // 2. Filter by jenis_id if user is bound to a specific jenis
                if ($user->jenis_id) {
                    if (Schema::hasColumn($table, 'jenis_id')) {
                        $builder->where($table . '.jenis_id', $user->jenis_id);
                    } elseif ($table === 'orang') {
                        // For Orang, jenis_id is inside orang_status pivot
                        $builder->whereHas('statusList', function($q) use ($user) {
                            $q->where('jenis_id', $user->jenis_id);
                        });
                    }
                }

                // 3. Filter by cabor_id if user is bound to a specific cabor
                if ($user->cabor_id) {
                    if (Schema::hasColumn($table, 'cabor_id')) {
                        $builder->where($table . '.cabor_id', $user->cabor_id);
                    } elseif ($table === 'orang') {
                        // For Orang, cabor_id is inside orang_status pivot
                        $builder->whereHas('statusList', function($q) use ($user) {
                            $q->where('cabor_id', $user->cabor_id);
                        });
                    } elseif ($table === 'events') {
                        // Event cabor is pivot event_cabor
                        $builder->whereHas('cabors', function($q) use ($user) {
                            $q->where('cabor_id', $user->cabor_id);
                        });
                    }
                }

                // 4. NPCI Scope: Only show disabilitas data
                $isNPCI = $user->hasRole('Ketua NPCI Provinsi') 
                    || $user->hasRole('Ketua NPCI Kab/Kota')
                    || $user->hasRole('Admin NPCI Provinsi');

                if ($isNPCI) {
                    if (Schema::hasColumn($table, 'disabilitas')) {
                        $builder->where($table . '.disabilitas', true);
                    }
                    if ($table === 'orang') {
                        $builder->where('orang.disabilitas', true);
                    }
                }
            }
        });
    }
}
