<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Orang;
use App\Models\Operator;
use Illuminate\Support\Facades\DB;

class EncryptExistingNik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sportif:encrypt-nik';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt plain-text NIKs in Orang and Operator tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting NIK encryption...');

        // Encrypt Orang
        $orangCount = DB::table('orang')->count();
        $this->info("Found {$orangCount} records in Orang table.");
        $bar = $this->output->createProgressBar($orangCount);
        $bar->start();

        DB::table('orang')->orderBy('id')->chunk(500, function ($orangs) use ($bar) {
            foreach ($orangs as $orang) {
                if ($orang->nik && strlen($orang->nik) <= 16) {
                    try {
                        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString($orang->nik);
                        DB::table('orang')->where('id', $orang->id)->update(['nik' => $encrypted]);
                    } catch (\Exception $e) {
                        // ignore if error
                    }
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nOrang NIKs encrypted.");

        // Encrypt Operator
        $operatorCount = DB::table('operators')->count();
        $this->info("Found {$operatorCount} records in Operator table.");
        $bar = $this->output->createProgressBar($operatorCount);
        $bar->start();

        DB::table('operators')->orderBy('id')->chunk(500, function ($operators) use ($bar) {
            foreach ($operators as $operator) {
                if ($operator->nik && strlen($operator->nik) <= 16) {
                    try {
                        $encrypted = \Illuminate\Support\Facades\Crypt::encryptString($operator->nik);
                        DB::table('operators')->where('id', $operator->id)->update(['nik' => $encrypted]);
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\nOperator NIKs encrypted.");

        $this->info("Encryption complete.");
    }
}
