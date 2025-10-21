<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;


class Deploy extends Command
{
    protected $signature = 'deploy';
    protected $description = 'Apply offline deploy package from /deploy folder';


    public function handle()
    {
        $fs = new Filesystem();
        $source = base_path('deploy');
        $target = base_path();
        if (!$fs->isDirectory($source)) {
            $this->error('No deploy folder found.');
            return 1;
        }
        $this->info('Copying updated files...');
        $fs->copyDirectory($source, $target);
        $this->info('Clearing and caching Laravel...');
        $this->call('optimize:clear');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->info('Deploy complete.');
        return 0;
    }
}
