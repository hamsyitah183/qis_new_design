<?php

// app/Console/Commands/PurgeTempUploads.php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeTempUploads extends Command
{
    protected $signature = 'documents:purge-temp-uploads';
    protected $description = 'Delete temp-uploaded files older than 24 hours';

    public function handle()
    {
        $dir = storage_path('app/temp-uploads');
        if (!is_dir($dir)) return;

        foreach (glob($dir . '/*') as $file) {
            if (is_file($file) && filemtime($file) < now()->subHours(24)->timestamp) {
                unlink($file);
            }
        }

        $this->info('Temp uploads purged.');
    }
}