<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ExportGedCom;

class GedcomResource
{
    public static function exportGedcom()
    {
        $user = Auth::user();
        if ($user) {
            Queue::push(new ExportGedCom($user));
        }
    }
}
