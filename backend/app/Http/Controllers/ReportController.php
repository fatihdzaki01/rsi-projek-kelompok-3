<?php

namespace App\Http\Controllers;

use App\Exports\FinancialReportExport;
use App\Http\Requests\ExportReportRequest;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    public function exportFinancial(ExportReportRequest $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $data     = $this->service->getReportData($request->tanggal_mulai, $request->tanggal_selesai);
        $filename = 'laporan-keuangan-' . $request->tanggal_mulai . '-' . $request->tanggal_selesai;

        if ($request->format_export === 'excel') {
            return Excel::download(
                new FinancialReportExport($data),
                $filename . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('reports.financial', $data)->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
