<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use App\Models\ConsignmentPermit;
use App\Models\InspectionApplication;
use App\Models\InspectionItem;
use App\Models\ConsignmentApplication;
use App\Services\PermitQrService;
use App\Services\ApplicationActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
// use PhpOffice\PhpWord\PhpWord;
// use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Spatie\Activitylog\Models\Activity;

class PermitGenerateController extends Controller
{
    public function __construct(private readonly PermitQrService $permitQrService) {}

    /**
     * Decode URL-safe permit slug back to stored permit_number.
     *
     * Examples:
     * - IPO_260... -> IPO/260...
     * - IPO260...  -> IPO/260...
     */
    private function decodePermitNumberSlug(string $slug): string
    {
        $decoded = $slug;

        // Backward compatibility for the older scheme (underscore instead of slash).
        if (str_contains($decoded, '_')) {
            $decoded = str_replace('_', '/', $decoded);
        }

        // New scheme: remove '/' entirely (e.g. IPO260...).
        if (!str_contains($decoded, '/')) {
            // Insert '/' between letter prefix and digit suffix.
            if (preg_match('/^([A-Za-z]+)(\d+)$/', $decoded, $m) === 1) {
                $decoded = $m[1] . '/' . $m[2];
            }
        }

        return $decoded;
    }

    //
    // public function generateWord()
    // {
    //     $phpWord = new PhpWord();

    //     $section = $phpWord->addSection();
    //     $section->addText('Hello from Laravel 12!');
    //     $section->addText('This is a generated Word document.');

    //     $fileName = 'laravel-generated.docx';

    //     header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    //     header("Content-Disposition: attachment; filename=\"$fileName\"");

    //     $writer = IOFactory::createWriter($phpWord, 'Word2007');
    //     $writer->save("php://output");
    //     exit;
    // }

    public function generatePermitWord($permit_number)
    {
        return $this->generatePermitPdf($permit_number);
    }

    public function generatePermitPdf($permit_number)
    {
        $decodedPermitNumber = $this->decodePermitNumberSlug((string) $permit_number);

        $permits = IpConsignmentPermit::where('permit_number', $decodedPermitNumber)->first();

        if (!$permits && is_numeric($permit_number)) {
            $permits = IpConsignmentPermit::where('id', $permit_number)->first();
        }
        if (!$permits) {
            abort(404, 'Permit not found');
        }

        $detail = $permits->consignment_detail;
        $application = $permits->application;
        $importer = $application->importer_detail;
        $exporter = $application->exporter;

        // Fetch condition
        $conditionItem = $permits->condition();
        $conditionText = $conditionItem ? $conditionItem->addional_condition : null;

        if ($conditionText) {
            $conditionText = str_replace(
                ['{{import_permit_number}}', '{{ import_permit_number }}', '{{year}}', '{{ year }}'],
                [$permits->permit_number, $permits->permit_number, now()->year, now()->year],
                $conditionText
            );
        }

        $validityDate = $permits->validity_date ? \Carbon\Carbon::parse($permits->validity_date)->format('d/M/Y') : '-';

        $qrDataUri = null;
        try {
            $encryptedPayload = $this->permitQrService->createEncryptedPayload((string) ($permits->permit_number ?? ''));
            $qrDataUri = $this->permitQrService->createQrDataUri($encryptedPayload);
        } catch (\Throwable $e) {
            Log::warning('Failed generating hardcopy permit QR: ' . $e->getMessage());
        }

        // Pass conditionText to view
        $pdf = Pdf::loadView('pdf.permit_pdf', compact(
            'permits',
            'detail',
            'application',
            'importer',
            'exporter',
            'qrDataUri',
            'conditionText',
            'validityDate'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream("Import_Permit_{$application->application_id}.pdf");
    }

    public function generateConsignmentPermitWord($permit_number)
    {
        $decodedPermitNumber = $this->decodePermitNumberSlug((string) $permit_number);

        $permits = ConsignmentPermit::with(['application.user', 'application.entryPoint'])
            ->where('permit_number', $decodedPermitNumber)
            ->first();

        // Backward compatibility: if an older frontend still passes numeric DB id.
        if (!$permits && is_numeric($permit_number)) {
            $permits = ConsignmentPermit::with(['application.user', 'application.entryPoint'])
                ->where('id', $permit_number)
                ->first();
        }

        if (!$permits) {
            abort(404, 'Permit not found');
        }

        $detail = $permits->consignment_detail;
        $application = $permits->application;
        $importer = $application->importer_detail;
        $exporter = $importer; // For consignment, both are the same user

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1200,
            'marginRight' => 1200,
        ]);

        /* ===============================
        HEADER TABLE
    =============================== */
        $logoTable = $section->addTable([
            'borderSize' => 1,
            'cellMargin' => 0,
            'borderColor' => '#FFFFFF',
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(public_path('/asset/jata-svg.jpg'), ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // CENTER TEXT
        $centerCell = $logoTable->addCell(8000, ['valign' => 'center']);
        $centerCell->addText('PLANT BIOSECURITY AND QUARANTINE DIVISION,', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('DEPARTMENT OF AGRICULTURE, SABAH, MALAYSIA', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addTextBreak(1);
        $centerCell->addText('CONSIGNMENT CERTIFICATE', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('REGULATED ARTICLES ', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // RIGHT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(public_path('/asset/sabah-svg.jpg'), ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $section->addTextBreak(1);
        $section->addText('Permit No.: ' . ($permits->permit_number ?? '-'), ['bold' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addTextBreak(1);

        /* ===============================
        PERMIT DETAILS
    =============================== */
        $section->addText('Consignee/Consignor: ' . strtoupper($application->user->fullname ?? '-'), ['size' => 11]);
        $section->addTextBreak(1);

        // Permission text with entry point
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Permission is hereby granted through ', ['size' => 11]);
        $textRun->addText(strtoupper($application->entryPoint->entry_name ?? '-'), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE, 'size' => 11]);

        $section->addTextBreak(2);

        /* ===============================
        SCHEDULE TABLE
    =============================== */
        $section->addText('Schedule:', ['size' => 11, 'bold' => true]);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ]);

        $table->addRow();
        $table->addCell(5000)->addText('Item Description', ['bold' => true, 'size' => 11]);
        $table->addCell(2000)->addText('Quantity', ['bold' => true, 'size' => 11]);
        $table->addCell(2500)->addText('Purpose', ['bold' => true, 'size' => 11]);

        $itemName = $detail['item_name'] ?? '-';

        $table->addRow();
        $table->addCell(5000)->addText($itemName, ['size' => 11]);
        $table->addCell(2000)->addText(($permits->quantity ?? '-') . ' ' . ($permits->unit_measurement ?? ''), ['size' => 11]);
        $table->addCell(2500)->addText($permits->purpose ?? '-', ['size' => 11]);

        $section->addTextBreak(2);

        /* ===============================
        FOOTER
    =============================== */
        $section->addText('Date of Issue: ' . now()->format('d/M/Y'), ['size' => 11]);
        $section->addTextBreak(2);
        $section->addText('Director of Agriculture', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addText('Sabah, Malaysia', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $fileName = "Consignment_Permit_{$permits->permit_number}.docx";
        $tempPath = storage_path("app/{$fileName}");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    // inspection application
    function generateInspectionWord($id)
    {
        // dd($id, InspectionItem::all());
        // $id is the inspection item id, not application id
        $application = InspectionApplication::where('application_id', $id)->firstOrFail();

        $items = $application->inspectionItems;

        // dd($application);

        // dd($items);

        $importer = $application->importer_detail;
        $exporter = $application->exporter;

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1200,
            'marginRight' => 1200,
        ]);

        /* ===============================
        HEADER TABLE
    =============================== */
        $logoTable = $section->addTable([
            'borderSize' => 1,
            'cellMargin' => 0,
            'borderColor' => '#FFFFFF',
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(public_path('/asset/jata-svg.jpg'), ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // CENTER TEXT
        $centerCell = $logoTable->addCell(8000, ['valign' => 'center']);
        $centerCell->addText('PLANT BIOSECURITY AND QUARANTINE DIVISION,', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('DEPARTMENT OF AGRICULTURE, SABAH, MALAYSIA', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addTextBreak(1);
        $centerCell->addText('PERMIT TO IMPORT ', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('REGULATED ARTICLES ', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('SIXTH / EIGHTH SCHEDULE', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('Regulations 3, 5(1) and 5(4)', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // RIGHT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(public_path('/asset/sabah-svg.jpg'), ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $section->addTextBreak(1);
        $section->addText('Permit No.:', ['bold' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addTextBreak(1);

        /* ===============================
        PERMIT DETAILS
    =============================== */
        // Name of consignee
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Name of consignee ', ['size' => 11]);
        $textRun->addText(str_pad(strtoupper($importer['fullname'] ?? '-'), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Address
        $textAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textAddress->addText('and address ', ['size' => 11]);
        $fullAddress = ($importer['address_1'] ?? '') . ', ' . ($importer['address_2'] ?? '') . ', ' . ($importer['postcode'] ?? '') . ' ' . ($importer['district'] ?? '') . ', ' . ($importer['state'] ?? '');
        $textAddress->addText(str_pad(strtoupper($fullAddress), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Name of consignor
        $textConsignor = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignor->addText('Name of consignor ', ['size' => 11]);
        $textConsignor->addText(str_pad(strtoupper($exporter['name'] ?? '-'), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Consignor address
        $textConsignorAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignorAddress->addText('add address ', ['size' => 11]);
        $textConsignorAddress->addText(str_pad(strtoupper($exporter['address'] ?? '-'), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Permission text with entry point underlined
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Permission is hereby granted to the consignee *soil/rooting compost/growing media/beneficial organisms contained in the Schedule hereto through ', ['size' => 11]);
        $textRun->addText(str_pad(strtoupper($application->entryPoint->entry_name ?? '-'), 50, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        $section->addText('List Inspection Certificates Items');

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
        ]);

        // Header row
        $table->addRow();
        $table->addCell(2500)->addText('Permit Number');
        $table->addCell(2500)->addText('Item Name');
        $table->addCell(3000)->addText('Purpose');
        $table->addCell(4000)->addText('Consignment Detail');

        foreach ($items as $item) {
            // Decode JSON string into array
            $consignment = $item['consignment_detail'];

            $itemName = $consignment['item_name'] ?? '-';

            // Make consignment detail a STRING (important!)
            $consignmentText = 'Quantity: ' . ($consignment['quantity'] ?? '-') . "\n" . 'Value: ' . ($consignment['value'] ?? '-') . "\n" . 'Measure: ' . ($consignment['measure'] ?? '-') . "\n" . 'Uses: ' . ($consignment['uses'] ?? '-');

            $table->addRow();
            $table->addCell(2500)->addText((string) ($item->permit_number ?? '-'));
            $table->addCell(2500)->addText((string) $itemName);
            $table->addCell(3000)->addText((string) ($item->purpose ?? '-'));
            $table->addCell(4000)->addText($consignmentText);
        }

        $section->addText('Date of Issue: ' . now()->format('d/M/Y'), [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addTextBreak(2);
        $section->addText('Director of Agriculture', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addText('Sabah, Malaysia', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $fileName = "Inspection_Certificate_{$application->application_id}.docx";
        $tempPath = storage_path("app/{$fileName}");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function generateInspection($id)
    {
        // $id = inspection application id
        $application = InspectionApplication::where('application_id', $id)->first();

        if (!$application) {
            abort(404, 'Inspection application not found');
        }

        $items = $application->inspectionItems;
        // dd( $items );
        $importer = $application->importer_detail;
        $exporter = $application->exporter;
        $entry = $application->entryPoint;

        $pdf = Pdf::loadView('pdf.permit_inspection', compact('application', 'items', 'importer', 'exporter', 'entry'))->setPaper('a4', 'portrait');

        return $pdf->stream("Inspection_Certificate_{$application->application_id}.pdf");
    }

    public function generateConsignmentApplication($id)
    {
        $application = ConsignmentApplication::where('application_id', $id)->first();

        if (!$application) {
            abort(404, 'Consignment application not found');
        }

        $items = $application->consignmentPermits;
        $importer = $application->importer;
        $exporter = $application->exporter;
        $entryPoint = $application->entryPoint; // renamed from $entry

        $permitNumber = $items->first()->permit_number ?? null;
        $validUntil = optional($items->first())->validity_date
            ? \Carbon\Carbon::parse($items->first()->validity_date)->format('d/M/Y')
            : '-';

        // TODO: replace with a real lookup once you have a District model.
        $district = $entryPoint ? $entryPoint->district : null;

        // TODO: replace with a real lookup for the officer authorised to sign
        $approvingOfficer = null;

        $pdf = Pdf::loadView(
            'pdf.permit_consignment',
            compact(
                'application',
                'items',
                'importer',
                'exporter',
                'entryPoint',      // now matches the blade
                'permitNumber',    // explicit permit number
                'validUntil',
                'district',
                'approvingOfficer',
            ),
        )->setPaper('a4', 'portrait');

        return $pdf->stream("Consignment_Permit_{$application->application_id}.pdf");
    }
    function generateConsignment($id)
    {
        $application = ConsignmentApplication::with(['importer', 'exporter', 'consignmentPermits'])
            ->where('application_id', $id)
            ->first();

        // dd($application);

        $items = $application->consignmentPermits;
        // dd($items);

        $importer = $application->importer_detail;
        $exporter = $application->exporter;

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 1000,
            'marginBottom' => 1000,
            'marginLeft' => 1200,
            'marginRight' => 1200,
        ]);

        /* ===============================
        HEADER TABLE
    =============================== */
        $logoTable = $section->addTable([
            'borderSize' => 1,
            'cellMargin' => 0,
            'borderColor' => '#FFFFFF',
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(public_path('/asset/jata-svg.jpg'), ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // CENTER TEXT
        $centerCell = $logoTable->addCell(8000, ['valign' => 'center']);
        $centerCell->addText('PLANT BIOSECURITY AND QUARANTINE DIVISION,', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('DEPARTMENT OF AGRICULTURE, SABAH, MALAYSIA', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addTextBreak(1);
        $centerCell->addText('PERMIT TO IMPORT ', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('REGULATED ARTICLES ', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('SIXTH / EIGHTH SCHEDULE', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('Regulations 3, 5(1) and 5(4)', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // RIGHT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(public_path('/asset/sabah-svg.jpg'), ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $section->addTextBreak(1);
        $section->addText('Permit No.:', ['bold' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addTextBreak(1);

        /* ===============================
        PERMIT DETAILS
    =============================== */
        // Name of consignee
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Name of consignee ', ['size' => 11]);
        $textRun->addText(str_pad(strtoupper($importer['fullname'] ?? '-'), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Address
        $textAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textAddress->addText('and address ', ['size' => 11]);
        $fullAddress = ($importer['address_1'] ?? '') . ', ' . ($importer['address_2'] ?? '') . ', ' . ($importer['postcode'] ?? '') . ' ' . ($importer['district'] ?? '') . ', ' . ($importer['state'] ?? '');
        $textAddress->addText(str_pad(strtoupper($fullAddress), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Name of consignor
        $textConsignor = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignor->addText('Name of consignor ', ['size' => 11]);
        $textConsignor->addText(str_pad(strtoupper($exporter['name'] ?? '-'), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Consignor address
        $textConsignorAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignorAddress->addText('add address ', ['size' => 11]);
        $textConsignorAddress->addText(str_pad(strtoupper($exporter['address'] ?? '-'), 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        // Permission text with entry point underlined
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Permission is hereby granted to the consignee *soil/rooting compost/growing media/beneficial organisms contained in the Schedule hereto through ', ['size' => 11]);
        $textRun->addText(str_pad(strtoupper($application->entryPoint->entry_name ?? '-'), 50, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

        $section->addTextBreak(1);

        $section->addText('List Consignment Certificates Items');

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
        ]);

        // Header row
        $table->addRow();
        $table->addCell(2500)->addText('Permit Number');
        $table->addCell(2500)->addText('Item Name');
        $table->addCell(3000)->addText('Purpose');
        $table->addCell(4000)->addText('Consignment Detail');

        foreach ($items as $item) {
            // Decode JSON string into array
            $consignment = $item['consignment_detail'];

            $itemName = $consignment['item_name'] ?? '-';

            // Make consignment detail a STRING (important!)
            $consignmentText = 'Quantity: ' . ($consignment['quantity'] ?? '-') . "\n" . 'Value: ' . ($consignment['value'] ?? '-') . "\n" . 'Measure: ' . ($consignment['measure'] ?? '-') . "\n" . 'Uses: ' . ($consignment['uses'] ?? '-');

            $table->addRow();
            $table->addCell(2500)->addText((string) ($item->permit_number ?? '-'));
            $table->addCell(2500)->addText((string) $itemName);
            $table->addCell(3000)->addText((string) ($item->purpose ?? '-'));
            $table->addCell(4000)->addText($consignmentText);
        }

        $section->addText('Date of Issue: ' . now()->format('d/M/Y'), [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addTextBreak(2);
        $section->addText('Director of Agriculture', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addText('Sabah, Malaysia', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $fileName = "Consignment_Certificate_{$application->application_id}.docx";
        $tempPath = storage_path("app/{$fileName}");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function permitCount(Request $request)
    {
        $type = $request->type;
        $id = $request->permit_number;
        $reason = $request->reason;

        $flag = null; // ✅ initialized

        if ($type == 'Import Permit') {
            $decodedPermitNumber = is_string($id) ? $this->decodePermitNumberSlug($id) : (string) $id;
            $permit = IpConsignmentPermit::where('permit_number', $decodedPermitNumber)->first();

            if (!$permit && is_numeric($id)) {
                $permit = IpConsignmentPermit::where('id', $id)->first();
            }

            if (!$permit) {
                return response()->json(['message' => 'Permit not found'], 404);
            }

            $flag = $this->countReason($permit, $reason);
            $application = $permit->application;

            ApplicationActivityLogger::log(
                application: $application,
                event: 'boundary_officer',
                description: 'A permit with id ' . $permit->permit_number . ' is downloaded by ' . authUser()['user']->fullname . '. (Reason:' . $reason . ') .',
                properties: ['role' => 'boundary officer'],
            );
            $application->logActivity('Printed', 'Permit with id ' . $permit->permit_number . ' is Printed with reason: ' . $reason, 'Printed');

            return $flag;
        }

        if ($type == 'Consignment') {
            $application = ConsignmentApplication::where('application_id', $id)->first();
            if (!$application) {
                return response()->json(['message' => 'Application not found'], 404);
            }

            $permits = $application->consignmentPermits;
            if (!$permits || $permits->isEmpty()) {
                return response()->json(['message' => 'No permits found for this application'], 404);
            }

            $flag = null;
            foreach ($permits as $permit) {
                $flag = $this->countReason($permit, $reason);
            }

            return $flag;
        }

        if ($type == 'Inspection') {
            $application = InspectionApplication::where('application_id', $id)->first();
            if (!$application) {
                return response()->json(['message' => 'Application not found'], 404);
            }

            $permits = $application->inspectionItems;
            if (!$permits || $permits->isEmpty()) {
                return response()->json(['message' => 'No inspection items found for this application'], 404);
            }

            $flag = null;
            foreach ($permits as $permit) {
                $flag = $this->countReason($permit, $reason);
            }

            return $flag;
        }

        // If type is unknown
        return response()->json(['message' => 'Invalid application type'], 400);
    }

    private function countReason($permit, $reason)
    {
        $count = $permit->print_calc;



        if ($count == 0 || $count == null) {

            $permit->print_calc = 1;
            $permit->save();



            return 'yes';
        } else {

            if ($reason) {
                $permit->print_calc = $count + 1;
                $permit->print_reason = $reason;
                $permit->save();


                return response()->json([
                    'message' => 'Add response'
                ]);
            } else {

                return response()->json([
                    'message' => 'Need Response',
                ]);
            }
        }
    }
}
