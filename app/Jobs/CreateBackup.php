<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateBackup implements ShouldQueue
{
    use Queueable;

    public $flag;

    public function __construct($flag)
    {
        $this->flag = $flag;
    }

    public function handle()
    {
        if ($this->flag === 'db') {
            Artisan::call('backup:run', ['--only-db' => true]);
        }
        if ($this->flag === 'files') {
            Artisan::call('backup:run', ['--only-files' => true]);
        }
        if ($this->flag === 'full') {
            Artisan::call('backup:run');
        }
    }
}
