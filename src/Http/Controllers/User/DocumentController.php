<?php

namespace Coderstm\Core\Http\Controllers\User;

use Coderstm\Core\Models\File;
use Illuminate\Http\Request;
use Coderstm\Core\Http\Controllers\Controller;

class DocumentController extends Controller
{
    /**
     * Get documents list
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $readDocuments = currentUser()->documents;
        $documents = File::whereIn('id', app_settings('documents')
            ->where('is_active', true)
            ->where('member', true)
            ->pluck('id'))->get()
            ->map(function ($item) use ($readDocuments) {
                $item->has_read = $readDocuments->contains('id', $item->id);
                return $item;
            });

        return response()->json($documents, 200);
    }

    /**
     * Read the specified document
     *
     * @param  \Coderstm\Core\Models\File  $document
     * @return \Illuminate\Http\Response
     */
    public function show(File $document)
    {
        currentUser()->documents()->syncWithoutDetaching([
            $document->id => [
                'read_at' => now()
            ]
        ]);
        return response()->json($document, 200);
    }
}
