<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\RecordManagement\Models\AnDuongRecord;

class TestAnDuongRecord extends Command
{
    protected $signature = 'test:an-duong-record';
    protected $description = 'Test AnDuongRecord model';

    public function handle()
    {
        try {
            $this->info('Testing AnDuongRecord model...');
            
            $count = AnDuongRecord::count();
            $this->info('Count: ' . $count);
            
            $records = AnDuongRecord::take(5)->get();
            $this->info('Records: ' . $records->count());
            
            foreach ($records as $record) {
                $this->info('Record ID: ' . $record->id . ', Year: ' . $record->year);
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
        }
    }
}