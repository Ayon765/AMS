<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackupController extends Controller
{
    private const SKIP = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    private function tables(): array
    {
        return array_values(array_filter(
            array_column(Schema::getTables(), 'name'),
            fn ($t) => ! in_array($t, self::SKIP) && ! str_starts_with($t, 'sqlite_')
        ));
    }

    public function index()
    {
        return view('backup.index', ['tables' => $this->tables()]);
    }

    public function download()
    {
        $payload = ['meta' => ['app' => 'assets-management-system', 'created_at' => now()->toDateTimeString()], 'tables' => []];

        foreach ($this->tables() as $t) {
            $payload['tables'][$t] = DB::table($t)->get()->map(fn ($r) => (array) $r)->all();
        }

        $name = 'backup-' . now()->format('Y-m-d_His') . '.json';

        return response()->streamDownload(
            fn () => print(json_encode($payload, JSON_UNESCAPED_UNICODE)),
            $name,
            ['Content-Type' => 'application/json']
        );
    }

    public function restore(Request $r)
    {
        $r->validate(['file' => 'required|file|max:51200', 'confirm' => 'accepted']);

        $payload = json_decode(file_get_contents($r->file('file')->getRealPath()), true);

        if (! is_array($payload) || ($payload['meta']['app'] ?? null) !== 'assets-management-system' || ! isset($payload['tables'])) {
            return back()->withErrors(['file' => 'Invalid backup file.']);
        }

        $known = $this->tables();

        Schema::disableForeignKeyConstraints();
        try {
            foreach ($payload['tables'] as $table => $rows) {
                if (! in_array($table, $known)) {
                    continue;
                }
                DB::table($table)->delete();
                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        auth()->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('login')->with('ok', 'Database restored. Please log in again.');
    }
}
