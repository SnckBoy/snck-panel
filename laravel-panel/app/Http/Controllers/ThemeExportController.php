<?php

namespace App\Http\Controllers;

use App\Services\ThemeCatalog;
use Illuminate\Http\Request;
use Throwable;

class ThemeExportController extends Controller
{
    public function __invoke(Request $request, string $theme, ThemeCatalog $catalog)
    {
        abort_unless($request->user()?->isStaff(), 403);
        abort_unless(preg_match('/^[a-z0-9][a-z0-9-]{1,63}$/', $theme), 404);
        try {
            $path = $catalog->export($theme);
        } catch (Throwable) {
            abort(404, 'Theme package could not be exported.');
        }

        return response()->download($path, $theme.'.zip', ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }
}
