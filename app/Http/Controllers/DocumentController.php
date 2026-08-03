<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function download(Request $request, Document $document): StreamedResponse
    {
        abort_unless(
            $request->user()->is($document->user) || $request->user()->hasRole('admin'),
            403,
        );
        abort_unless($document->disk === 'local', 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download(
            $document->path,
            $document->original_name,
            ['Content-Type' => $document->mime_type],
        );
    }
}
