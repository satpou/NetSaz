<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SyncWithGithub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:sync {--force : Paksa update meskipun hash lokal dan remote sama}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa dan sinkronisasikan kode di VM dengan repositori GitHub origin/main';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $basePath = base_path();

        // 1. Fetch remote origin/main
        $fetch = Process::path($basePath)->run('git fetch origin main');
        if (!$fetch->successful()) {
            $err = trim($fetch->errorOutput());
            $this->error("Gagal melakukan fetch dari GitHub: {$err}");
            Log::error('Git sync fetch failed', ['output' => $err]);
            return Command::FAILURE;
        }

        // 2. Bandingkan commit hash lokal vs remote
        $localHash = trim(Process::path($basePath)->run('git rev-parse HEAD')->output());
        $remoteHash = trim(Process::path($basePath)->run('git rev-parse origin/main')->output());

        if (empty($remoteHash)) {
            $this->error('Tidak dapat membaca hash remote origin/main.');
            return Command::FAILURE;
        }

        if ($localHash === $remoteHash && !$this->option('force')) {
            $this->info("Server sudah sinkron dengan GitHub origin/main ({$localHash}).");
            return Command::SUCCESS;
        }

        $this->warn("Perubahan terdeteksi! Sinkronisasi: {$localHash} -> {$remoteHash}");
        Log::info("Git sync starting: {$localHash} -> {$remoteHash}");

        // 3. Reset hard ke origin/main dan bersihkan untracked files yang bentrok
        $reset = Process::path($basePath)->run('git reset --hard origin/main');
        if (!$reset->successful()) {
            $err = trim($reset->errorOutput());
            $this->error("Gagal melakukan reset ke origin/main: {$err}");
            Log::error('Git sync reset failed', ['output' => $err]);
            return Command::FAILURE;
        }

        // 4. Jalankan migrasi database bila ada migrasi baru
        $migrate = Process::path($basePath)->run('php artisan migrate --force');
        if (!$migrate->successful()) {
            Log::warning('Git sync migration warning', ['output' => $migrate->errorOutput()]);
        }

        // 5. Segarkan cache Laravel (config, routes, views)
        Process::path($basePath)->run('php artisan config:cache');
        Process::path($basePath)->run('php artisan route:cache');
        Process::path($basePath)->run('php artisan view:cache');

        $latestCommit = trim(Process::path($basePath)->run('git log -1 --pretty=format:"%h - %s (%ci)"')->output());
        $msg = "Sinkronisasi berhasil! Commit sekarang: {$latestCommit}";
        $this->info($msg);
        Log::info("Git sync completed successfully: {$latestCommit}");

        return Command::SUCCESS;
    }
}
