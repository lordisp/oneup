<?php

namespace App\Http\Livewire\Admin\Controllers;

use Livewire\FileUploadConfiguration;

class FileUploadSignature extends \Livewire\Controllers\FileUploadHandler
{
    public function handle()
    {
        // abort_unless(request()->hasValidSignature(), 401);

        $disk = FileUploadConfiguration::disk();

        $filePaths = $this->validateAndStore(request('files'), $disk);

        return ['paths' => $filePaths];
    }
}