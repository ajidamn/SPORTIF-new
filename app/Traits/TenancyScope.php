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
                    // Cek jika model ini menggunakan SoftDeletes, sertakan data yang terhapus
                    if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(get_class($builder->getModel())))) {
                        $builder->withTrashed();
                    }
                    return;
                }

                $table = $builder->getModel()->getTable();

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
