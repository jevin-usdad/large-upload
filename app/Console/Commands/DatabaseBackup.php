<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$path}";

        exec($command, $output, $result);

        if ($result !== 0) {
            $this->error('Backup failed!');
            return 1; // important: failure exit code
        }

        $this->info('Backup completed successfully.');
        return 0;
    }
}
