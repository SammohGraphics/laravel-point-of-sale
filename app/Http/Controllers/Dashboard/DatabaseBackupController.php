<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use File;
use Artisan;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        // Ensure the directory exists before loading files
        $backupDir = storage_path('/app/POS');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::allFiles($backupDir);

        return view('database.index', compact('files'));
    }

    public function create()
    {
        try {
            // Run backup command programmatically
            Artisan::call('backup:run', ['--only-db' => true]);

            return Redirect::route('backup.index')->with('success', 'Database Backup Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('backup.index')->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    public function download(String $getFileName)
    {
        $path = storage_path('app/POS/' . $getFileName);

        // Check if the file exists
        if (File::exists($path)) {
            return response()->download($path);
        } else {
            return Redirect::route('backup.index')->with('error', 'File not found.');
        }
    }

    public function delete(String $getFileName)
    {
        try {
            Storage::delete('POS/' . $getFileName);

            return Redirect::route('backup.index')->with('success', 'Backup Deleted Successfully!');
        } catch (\Exception $e) {
            return Redirect::route('backup.index')->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }
}

