<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use ZipArchive;
use Exception;

class TelegramBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza un backup de la base de datos SQLite/MySQL en caliente, la comprime en ZIP y la envía por Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de backup...');

        $connection = config('database.default');
        $this->info("Conexión de base de datos activa: {$connection}");

        $date = Carbon::now('America/Bogota')->format('Y-m-d_H-i-s');
        $appName = config('app.name', 'Surtitornillos');

        $storagePath = storage_path('app/backups');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $backupFileBaseName = "backup_{$appName}_{$connection}_{$date}";
        $zipFileName = "{$backupFileBaseName}.zip";
        $zipPath = "{$storagePath}/{$zipFileName}";

        try {
            if ($connection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');

                if ($dbPath === ':memory:') {
                    $this->error('No se puede respaldar una base de datos SQLite en memoria.');
                    return Command::FAILURE;
                }

                if (!file_exists($dbPath)) {
                    $this->error("La base de datos SQLite no existe en la ruta: {$dbPath}");
                    return Command::FAILURE;
                }

                $tempSqliteName = "{$backupFileBaseName}.sqlite";
                $tempSqlitePath = "{$storagePath}/{$tempSqliteName}";

                // Eliminar el archivo temporal si existe para que VACUUM INTO no falle
                if (file_exists($tempSqlitePath)) {
                    @unlink($tempSqlitePath);
                }

                $this->info('Clonando base de datos SQLite en caliente...');
                // VACUUM INTO es ideal porque realiza una copia limpia y optimizada sin bloquear escrituras
                DB::statement("VACUUM INTO '{$tempSqlitePath}'");

                if (!file_exists($tempSqlitePath) || filesize($tempSqlitePath) === 0) {
                    $this->error('Fallo al crear la copia de la base de datos SQLite.');
                    return Command::FAILURE;
                }

                // Comprimir en ZIP
                $this->info('Comprimiendo en ZIP...');
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFile($tempSqlitePath, "database.sqlite");
                    $zip->close();
                } else {
                    $this->error('No se pudo crear el archivo ZIP.');
                    @unlink($tempSqlitePath);
                    return Command::FAILURE;
                }

                // Limpiar el SQLite temporal clonado ya que el ZIP está listo
                @unlink($tempSqlitePath);

            } elseif ($connection === 'mysql') {
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');
                $host = config('database.connections.mysql.host', '127.0.0.1');
                $port = config('database.connections.mysql.port', '3306');

                $sqlFileName = "{$backupFileBaseName}.sql";
                $sqlPath = "{$storagePath}/{$sqlFileName}";

                $passwordArg = empty($password) ? '' : "-p\"{$password}\"";
                $command = "mysqldump -h {$host} -P {$port} -u {$username} {$passwordArg} {$database} > \"{$sqlPath}\"";

                $this->info('Ejecutando volcado MySQL...');
                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !file_exists($sqlPath) || filesize($sqlPath) === 0) {
                    $this->error("Fallo al crear el volcado MySQL. Asegúrate de tener 'mysqldump' instalado.");
                    return Command::FAILURE;
                }

                // Comprimir en ZIP
                $this->info('Comprimiendo en ZIP...');
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFile($sqlPath, $sqlFileName);
                    $zip->close();
                } else {
                    $this->error('No se pudo crear el archivo ZIP.');
                    @unlink($sqlPath);
                    return Command::FAILURE;
                }

                // Limpiar el SQL temporal
                @unlink($sqlPath);

            } else {
                $this->error("El motor de base de datos '{$connection}' no es soportado por este comando de backup.");
                return Command::FAILURE;
            }

            // Enviar a Telegram
            $this->info('Enviando a Telegram...');
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            if (!$botToken || !$chatId) {
                $this->error('Faltan las credenciales de Telegram (TELEGRAM_BOT_TOKEN o TELEGRAM_CHAT_ID) en el archivo .env.');
                @unlink($zipPath);
                return Command::FAILURE;
            }

            $url = "https://api.telegram.org/bot{$botToken}/sendDocument";
            $caption = "🔒 Backup Automático DB\n🗄 App: {$appName}\n📅 Fecha: " . Carbon::now('America/Bogota')->format('d/M/Y H:i A') . "\n📦 Motor: {$connection}";

            $request = Http::asMultipart();
            if (config('app.env') === 'local') {
                $request = $request->withoutVerifying();
            }

            $response = $request->attach(
                'document', file_get_contents($zipPath), $zipFileName
            )->post($url, [
                'chat_id' => $chatId,
                'caption' => $caption,
            ]);


            if ($response->successful()) {
                $this->info('¡Backup enviado a Telegram exitosamente!');
            } else {
                $this->error('Error enviando a Telegram: ' . $response->body());
                @unlink($zipPath);
                return Command::FAILURE;
            }

            // Limpiar ZIP final
            @unlink($zipPath);
            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Ocurrió un error inesperado durante el backup: ' . $e->getMessage());
            if (isset($tempSqlitePath) && file_exists($tempSqlitePath)) {
                @unlink($tempSqlitePath);
            }
            if (isset($sqlPath) && file_exists($sqlPath)) {
                @unlink($sqlPath);
            }
            if (isset($zipPath) && file_exists($zipPath)) {
                @unlink($zipPath);
            }
            return Command::FAILURE;
        }
    }
}
