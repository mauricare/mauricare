<?php

use App\Models\Document;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$documents = Document::query()->get(['disk', 'path']);
$available = $documents->filter(
    fn (Document $document): bool => Storage::disk($document->disk)->exists($document->path),
)->count();

echo 'document_records: '.$documents->count().PHP_EOL;
echo 'document_files_available: '.$available.PHP_EOL;
echo 'document_files_missing: '.($documents->count() - $available).PHP_EOL;
