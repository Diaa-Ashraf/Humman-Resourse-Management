<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\DataRetentionPolicy;
use Illuminate\Support\Facades\DB;
use App\Models\{Branch,EmployeeDocument,EmployeeRequest,JobOpening};
use App\Services\{ApprovalNotifier,BranchAccess};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('governance:prune', function () {
    $policies = DataRetentionPolicy::where('cleanup_enabled', true)->whereNotNull('retention_days')->get()->keyBy('data_category');
    foreach (['audit_logs' => 'created_at', 'user_sessions' => 'last_seen_at'] as $category => $dateColumn) {
        $policy = $policies->get($category);
        if (!$policy) continue;
        $deleted = DB::table($category)->whereNotNull($dateColumn)->where($dateColumn, '<', now()->subDays($policy->retention_days))->delete();
        $this->info("{$category}: {$deleted}");
    }
})->purpose('Apply enabled retention policies to audit and expired session records');

Schedule::command('governance:prune')->daily();

Artisan::command('hrms:notify-operations', function (ApprovalNotifier $notifier, BranchAccess $access) {
    foreach (Branch::where('is_active', true)->get() as $branch) {
        $scope = EmployeeDocument::whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id));
        $dueDocuments = (clone $scope)->whereNotNull('expires_at')->whereBetween('expires_at', [today(), today()->addDays(30)])->count();
        $expiredDocuments = (clone $scope)->whereNotNull('expires_at')->whereDate('expires_at', '<', today())->count();
        if ($dueDocuments + $expiredDocuments > 0) {
            $notifier->branchApprovers($branch->id, 'show_employee_documents', 'تنبيه مستندات فرع '.$branch->name, "{$expiredDocuments} مستند منتهي و{$dueDocuments} مستندًا ينتهي خلال 30 يومًا.", route('employee-documents.index'));
        }
        $overdueRequests = EmployeeRequest::where('status', 'pending')->where('created_at', '<', now()->subDays(5))->whereHas('employee', fn ($q) => $q->where('branch_id', $branch->id))->count();
        if ($overdueRequests > 0) {
            $notifier->branchApprovers($branch->id, 'review_employee_requests', 'طلبات موظفين متأخرة في '.$branch->name, "يوجد {$overdueRequests} طلبًا بانتظار المراجعة منذ أكثر من 5 أيام.", route('employee-requests.index'));
        }
        $closingVacancies = JobOpening::where('branch_id', $branch->id)->where('status', 'open')->where('approval_status', 'approved')->whereBetween('closing_date', [today(), today()->addDays(7)])->count();
        if ($closingVacancies > 0) {
            $notifier->branchApprovers($branch->id, 'show_recruitment', 'شواغر تقترب من موعد الإغلاق', "يوجد {$closingVacancies} شاغرًا في فرع {$branch->name} سيغلق خلال 7 أيام.", route('recruitment.index'));
        }
    }
    $this->info('تم إصدار تنبيهات التشغيل حسب نطاق الفرع.');
})->purpose('Notify authorized branch users about expiring documents, overdue requests, and closing vacancies');

Schedule::command('hrms:notify-operations')->dailyAt('08:00');

Artisan::command('hrms:backup', function () {
    $connection = config('database.default');
    $settings = config("database.connections.{$connection}");
    $runDump = function (string $binary, array $arguments, array $environment): string {
        $process = new Process(array_merge([$binary], $arguments), base_path(), $environment);
        $process->setTimeout(600);
        $process->mustRun();
        return $process->getOutput();
    };
    $sqliteDump = function (string $databasePath): string {
        $snapshot = tempnam(sys_get_temp_dir(), 'hrms-sqlite-snapshot-');@unlink($snapshot);
        try { $escaped=str_replace("'", "''", $snapshot);DB::connection()->getPdo()->exec("VACUUM INTO '{$escaped}'");$contents=file_get_contents($snapshot);if($contents===false)throw new RuntimeException('تعذر قراءة لقطة SQLite.');return $contents; }
        finally { @unlink($snapshot); }
    };
    $mysqlDump = function () use ($runDump, $settings): string {
        try {
            return $runDump('mysqldump', ['--single-transaction', '--skip-lock-tables', '--host='.$settings['host'], '--port='.$settings['port'], '--user='.$settings['username'], $settings['database']], ['MYSQL_PWD' => $settings['password'] ?? '']);
        } catch (Throwable $exception) {
            Log::warning('mysqldump utility unavailable or failed; using the PDO snapshot exporter.', ['reason'=>$exception->getMessage()]);
        }
        $sql="SET FOREIGN_KEY_CHECKS=0;\n";$pdo=DB::connection()->getPdo();DB::beginTransaction();
        try {
            foreach (DB::select('SHOW FULL TABLES') as $row) {
                $columns=(array)$row;$tableName=(string)reset($columns);$tableType=(string)(array_values($columns)[1]??'BASE TABLE');
                if($tableType!=='BASE TABLE')continue;
                $quoted='`'.str_replace('`','``',$tableName).'`';$definition=(array)DB::selectOne('SHOW CREATE TABLE '.$quoted);$createSql=$definition['Create Table']??array_values($definition)[1]??null;
                if(!$createSql)throw new RuntimeException('تعذر قراءة بنية الجدول '.$tableName);
                $sql.="DROP TABLE IF EXISTS {$quoted};\n{$createSql};\n";$rows=DB::table($tableName)->get();if($rows->isEmpty())continue;
                $fieldNames=array_keys((array)$rows->first());$fieldSql=implode(',',array_map(fn($field)=>'`'.str_replace('`','``',$field).'`',$fieldNames));
                foreach($rows as $record){$values=[];foreach((array)$record as $value)$values[]=$value===null?'NULL':$pdo->quote((string)$value);$sql.='INSERT INTO '.$quoted.' ('.$fieldSql.') VALUES ('.implode(',',$values).");\n";}
            }
            $sql.="SET FOREIGN_KEY_CHECKS=1;\n";
        } finally { if(DB::transactionLevel()>0)DB::rollBack(); }
        return $sql;
    };
    $payload = match ($settings['driver'] ?? '') {
        'sqlite' => $sqliteDump($settings['database']),
        'mysql', 'mariadb' => $mysqlDump(),
        'pgsql' => $runDump('pg_dump', ['--no-owner', '--no-acl', '--host='.$settings['host'], '--port='.$settings['port'], '--username='.$settings['username'], $settings['database']], ['PGPASSWORD' => $settings['password'] ?? '']),
        default => throw new RuntimeException('نوع قاعدة البيانات الحالي غير مدعوم لعمل النسخة الاحتياطية.'),
    };
    if (!$payload) throw new RuntimeException('خرجت نسخة قاعدة البيانات فارغة.');
    $archivePath = tempnam(sys_get_temp_dir(), 'hrms-backup-');$archive = new ZipArchive();
    if ($archive->open($archivePath, ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) throw new RuntimeException('تعذر إنشاء ملف أرشيف النسخة.');
    $archive->addFromString('database.dump', $payload);$fileCount=0;$sourceDisk=Storage::disk(config('filesystems.default'));
    foreach ($sourceDisk->allFiles() as $storedPath) {
        if (str_starts_with($storedPath,'backups/')) continue;
        $stream=$sourceDisk->readStream($storedPath);if(!is_resource($stream))continue;
        try{$contents=stream_get_contents($stream);if($contents===false)throw new RuntimeException('تعذرت قراءة ملف تخزين: '.$storedPath);$archive->addFromString('files/'.$storedPath,$contents);$fileCount++;}
        finally{fclose($stream);}
    }
    $archive->close();$archiveBytes=file_get_contents($archivePath);@unlink($archivePath);
    $encrypted = Crypt::encryptString($archiveBytes);
    $relative = 'backups/hrms-'.now()->format('Ymd-His').'.backup.enc';
    $disk = Storage::disk(config('hrms.backup_disk'));
    if (!$disk->put($relative, $encrypted)) throw new RuntimeException('تعذر حفظ النسخة على وسيط التخزين المحدد.');
    $disk->put($relative.'.json', json_encode(['created_at'=>now()->toIso8601String(),'database'=>$connection,'sha256'=>hash('sha256',$encrypted),'bytes'=>strlen($encrypted),'private_files'=>$fileCount], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    $cutoff=now()->subDays((int)config('hrms.backup_retention_days',14))->timestamp;
    foreach($disk->files('backups') as $oldFile)if(str_ends_with($oldFile,'.backup.enc')&&$disk->lastModified($oldFile)<$cutoff){$disk->delete([$oldFile,$oldFile.'.json']);}
    $this->info('تم حفظ نسخة مشفرة: '.$relative.' على القرص '.config('hrms.backup_disk'));
})->purpose('Create an encrypted database backup on the configured filesystem disk');

Artisan::command('hrms:backup-verify {file?}', function (?string $file = null) {
    $disk = Storage::disk(config('hrms.backup_disk'));
    if (!$file) $file = collect($disk->files('backups'))->filter(fn ($path) => str_ends_with($path, '.backup.enc'))->sortDesc()->first();
    if (!$file || str_contains($file, '..') || !str_starts_with($file, 'backups/')) throw new RuntimeException('حدد ملف نسخة احتياطية صالحًا داخل مجلد backups.');
    if (!$disk->exists($file)) throw new RuntimeException('ملف النسخة غير موجود.');
    $encrypted = $disk->get($file);$metadata = json_decode($disk->get($file.'.json'), true);
    if (!hash_equals((string)($metadata['sha256'] ?? ''), hash('sha256', $encrypted))) throw new RuntimeException('فشل التحقق من بصمة النسخة.');
    $archivePath=tempnam(sys_get_temp_dir(),'hrms-verify-');file_put_contents($archivePath,Crypt::decryptString($encrypted));$archive=new ZipArchive();
    if($archive->open($archivePath)!==true)throw new RuntimeException('تعذر فتح أرشيف النسخة.');$payload=$archive->getFromName('database.dump');if(!$payload)throw new RuntimeException('النسخة لا تحتوي تفريغ قاعدة البيانات.');
    if ((config('database.connections.'.($metadata['database']??'').'.driver')) === 'sqlite') {
        $temp = tempnam(sys_get_temp_dir(), 'hrms-restore-check-');file_put_contents($temp, $payload);
        try { $pdo = new PDO('sqlite:'.$temp);$result = $pdo->query('PRAGMA quick_check')->fetchColumn();if($result!=='ok')throw new RuntimeException('فشل فحص سلامة قاعدة SQLite المستعادة.'); }
        finally { @unlink($temp); }
    }
    $archive->close();@unlink($archivePath);
    $this->info('نجح فك التشفير والتحقق من سلامة محتوى النسخة: '.$file);
})->purpose('Verify a backup checksum, decryption, and SQLite integrity on an isolated temporary copy');

Schedule::command('hrms:backup')->dailyAt('02:00')->onFailure(fn()=>Log::critical('HRMS scheduled backup failed.'));
Schedule::command('hrms:backup-verify')->dailyAt('03:00')->onFailure(fn()=>Log::critical('HRMS backup verification failed.'));
