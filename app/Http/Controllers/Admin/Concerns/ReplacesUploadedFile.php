<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ReplacesUploadedFile
{
    /**
     * Stores a newly uploaded file (or clears it when "remove_{field}" is ticked) and deletes the old one.
     * Returns the new path, null when removed, or false when nothing changed.
     */
    protected function replaceUpload(Request $request, string $field, ?string $currentPath, string $directory): string|false|null
    {
        if ($request->hasFile($field)) {
            $path = $request->file($field)->store($directory, 'public');
        } elseif ($request->boolean('remove_'.$field)) {
            $path = null;
        } else {
            return false;
        }

        if ($currentPath !== null) {
            Storage::disk('public')->delete($currentPath);
        }

        return $path;
    }
}
