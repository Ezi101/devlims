<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PrintControllerLog extends Controller
{
    public function logPrintEvent(Request $request)
    {
        $documentId = $request->documentID;
        $printedModule = $request->printedModule;

        $parts = preg_split('/\s+|-/', $documentId);

        $initials = '';
        $numericPart = '';
        foreach ($parts as $part) {
            if (ctype_alpha($part)) {
                $initials .= strtoupper($part[0]);
            } elseif (ctype_digit($part)) {
                $numericPart .= $part;
            }
        }

        $newDocumentId = $initials . $numericPart;

        $printedAt = Carbon::now()->format('F j, Y h:i:s A');
        $documentId = session('documentID', '');
        session()->flash('documentID', $newDocumentId);

        AuditLogger::log('printed', $printedModule, ' ID: ' . $newDocumentId . ', was printed at ' . $printedAt);

        return response()->json(['success' => true, 'documentID' => $newDocumentId]);
    }
}
