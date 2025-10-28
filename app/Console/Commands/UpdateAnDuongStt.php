<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\RecordManagement\Models\AnDuongRecord;

class UpdateAnDuongStt extends Command
{
    protected $signature = 'update:an-duong-stt';
    protected $description = 'Update STT for existing AnDuongRecord records';

    public function handle()
    {
        $this->info('Updating STT for existing records...');
        
        $records = AnDuongRecord::whereNull('stt')->orWhere('stt', 0)->get();
        
        foreach ($records as $index => $record) {
            $record->stt = $index + 1;
            $record->save();
            $this->info('Updated record ID: ' . $record->id . ' with STT: ' . $record->stt);
        }
        
        $this->info('Update completed!');
    }
}