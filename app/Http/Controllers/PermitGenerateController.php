<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use App\Models\ConsignmentPermit;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class PermitGenerateController extends Controller
{
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

    public function generatePermitWord($id)
    {
        $permits = IpConsignmentPermit::where('id', $id)->first();
        $detail = $permits->consignment_detail;
        $application = $permits->application;
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
            'borderColor' => '#FFFFFF'
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/jata-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

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
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/sabah-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $section->addTextBreak(1);
        $section->addText('Permit No.:', ['bold' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addTextBreak(1);

        /* ===============================
        PERMIT DETAILS
    =============================== */
        // Name of consignee
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Name of consignee ', ['size' => 11]);
        $textRun->addText(
            str_pad(strtoupper($importer['fullname'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Address
        $textAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textAddress->addText('and address ', ['size' => 11]);
        $fullAddress = ($importer['address_1'] ?? '') .
            ', ' . ($importer['address_2'] ?? '') .
            ', ' . ($importer['postcode'] ?? '') . ' ' . ($importer['district'] ?? '') .
            ', ' . ($importer['state'] ?? '');
        $textAddress->addText(
            str_pad(strtoupper($fullAddress), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Name of consignor
        $textConsignor = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignor->addText('Name of consignor ', ['size' => 11]);
        $textConsignor->addText(
            str_pad(strtoupper($exporter['name'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Consignor address
        $textConsignorAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignorAddress->addText('add address ', ['size' => 11]);
        $textConsignorAddress->addText(
            str_pad(strtoupper($exporter['address'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Permission text with entry point underlined
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText(
            'Permission is hereby granted to the consignee *soil/rooting compost/growing media/beneficial organisms contained in the Schedule hereto through ',
            ['size' => 11]
        );
        $textRun->addText(
            str_pad(strtoupper($application->entryPoint->entry_name ?? '-'), 50, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        /* ===============================
        CONDITIONS
    =============================== */
        $section->addText(
            'This permit is issued subject to the following conditions:',
            ['bold' => true],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]
        );

        $conditions = [
            'Import license must be obtained from the relevant Ministry.',
            'A copy of this Import Permit must accompany the consignment.',
            'The regulated articles are subject to inspection prior to clearance.',
            'This permit is valid until for one consignment only.',
            'The consignment must be accompanied by a Phytosanitary Certificate or a statement from the official Plant Protection Service of the country of origin bearing the following certificate:',
            'Further conditions'
        ];

        foreach ($conditions as $i => $condition) {

            // Special case for the "until" line
            if (str_contains($condition, 'until')) {
                $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
                $textRun->addText(($i + 1) . ". ", ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

                $parts = explode('until', $condition, 2);
                $textRun->addText($parts[0] . 'until ', ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
                $textRun->addText(str_pad('', 50, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);
                if (isset($parts[1])) {
                    $textRun->addText(trim($parts[1]), ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
                }
            }
            // Special case for the "(a) Treatment" and "(b) Other declaration"
            elseif ($i === 4) {
                // First, print the main text for condition 5
                $section->addText(($i + 1) . ". " . trim($condition), ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

                // Subpoints (a) and (b) with two dotted lines each
                $subpoints = [
                    '(a) Treatment',
                    '(b) Other declaration'
                ];

                foreach ($subpoints as $sub) {
                    // Create a TextRun for the label + first dotted line
                    $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
                    $textRun->addText($sub . ' ', ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
                    $textRun->addText(str_pad('', 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);

                    // Add the **second dotted line** in a new TextRun
                    $secondLine = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
                    $secondLine->addText(str_pad('', 100, ' '), ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]);
                }
            }
            // Normal conditions
            else {
                $section->addText(
                    ($i + 1) . ". " . $condition,
                    ['size' => 11],
                    ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]
                );
            }
        }





        $section->addTextBreak(4);

        /* ===============================
        SCHEDULE TABLE
    =============================== */
        $section->addText('Schedule:', ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80
        ]);

        // dd($detail);


        $table->addRow();
        $table->addCell(5000)->addText('Descriptions', ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $table->addCell(2000)->addText('Quantity', ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $table->addCell(2500)->addText('Country of Origin', ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

        // Extract item name after dash
        $itemName = $detail['item_name'] ?? '-';
        $parts = explode('-', $itemName);
        $afterDash = isset($parts[1]) ? trim($parts[1]) : $itemName;

        // Table row
        $table->addRow();
        $table->addCell(5000)->addText($afterDash, ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $table->addCell(2000)->addText(($permits['quantity'] ?? '-') . ' ' . ($permits['unit_measurement'] ?? ''), ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

        $country = Country::select('name')->where('code', $exporter['country'])->first();
        $table->addCell(2500)->addText($country->name ?? '-', ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

        $section->addTextBreak(2);


        /* ===============================
        FOOTER
    =============================== */
        $section->addText("Date of Issue: " . now()->format('d/m/Y'), [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addTextBreak(2);
        $section->addText('Director of Agriculture', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addText('Sabah, Malaysia', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $fileName = "Import_Permit_{$application->application_id}.docx";
        $tempPath = storage_path("app/{$fileName}");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function generateConsignmentPermitWord($id)
    {
        $permits = ConsignmentPermit::with(['application.user', 'application.entryPoint'])->where('id', $id)->first();

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
            'borderColor' => '#FFFFFF'
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/jata-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        // CENTER TEXT
        $centerCell = $logoTable->addCell(8000, ['valign' => 'center']);
        $centerCell->addText('PLANT BIOSECURITY AND QUARANTINE DIVISION,', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('DEPARTMENT OF AGRICULTURE, SABAH, MALAYSIA', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addTextBreak(1);
        $centerCell->addText('CONSIGNMENT CERTIFICATE', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $centerCell->addText('REGULATED ARTICLES ', ['bold' => true, 'size' => 15], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // RIGHT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/sabah-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

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
        $textRun->addText(
            'Permission is hereby granted through ',
            ['size' => 11]
        );
        $textRun->addText(
            strtoupper($application->entryPoint->entry_name ?? '-'),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE, 'size' => 11]
        );

        $section->addTextBreak(2);

        /* ===============================
        SCHEDULE TABLE
    =============================== */
        $section->addText('Schedule:', ['size' => 11, 'bold' => true]);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80
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
        $section->addText("Date of Issue: " . now()->format('d/m/Y'), ['size' => 11]);
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
    function generateInspection($id)
    {
        $application = InspectionApplication::with(['importer', 'exporter', 'inspectionItems'])
        ->where('application_id', $id)->first();

        // dd($application);

        $items = $application->inspectionItems;
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
            'borderColor' => '#FFFFFF'
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/jata-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

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
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/sabah-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $section->addTextBreak(1);
        $section->addText('Permit No.:', ['bold' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addTextBreak(1);

        /* ===============================
        PERMIT DETAILS
    =============================== */
        // Name of consignee
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Name of consignee ', ['size' => 11]);
        $textRun->addText(
            str_pad(strtoupper($importer['fullname'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Address
        $textAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textAddress->addText('and address ', ['size' => 11]);
        $fullAddress = ($importer['address_1'] ?? '') .
            ', ' . ($importer['address_2'] ?? '') .
            ', ' . ($importer['postcode'] ?? '') . ' ' . ($importer['district'] ?? '') .
            ', ' . ($importer['state'] ?? '');
        $textAddress->addText(
            str_pad(strtoupper($fullAddress), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Name of consignor
        $textConsignor = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignor->addText('Name of consignor ', ['size' => 11]);
        $textConsignor->addText(
            str_pad(strtoupper($exporter['name'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Consignor address
        $textConsignorAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignorAddress->addText('add address ', ['size' => 11]);
        $textConsignorAddress->addText(
            str_pad(strtoupper($exporter['address'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Permission text with entry point underlined
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText(
            'Permission is hereby granted to the consignee *soil/rooting compost/growing media/beneficial organisms contained in the Schedule hereto through ',
            ['size' => 11]
        );
        $textRun->addText(
            str_pad(strtoupper($application->entryPoint->entry_name ?? '-'), 50, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

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
            $consignmentText =
                'Quantity: ' . ($consignment['quantity'] ?? '-') . "\n" .
                'Value: ' . ($consignment['value'] ?? '-') . "\n" .
                'Measure: ' . ($consignment['measure'] ?? '-') . "\n" .
                'Uses: ' . ($consignment['uses'] ?? '-');
        
            $table->addRow();
            $table->addCell(2500)->addText((string) ($item->permit_number ?? '-'));
            $table->addCell(2500)->addText((string) $itemName);
            $table->addCell(3000)->addText((string) ($item->purpose ?? '-'));
            $table->addCell(4000)->addText($consignmentText);
        }
        
        

        $section->addText("Date of Issue: " . now()->format('d/m/Y'), [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addTextBreak(2);
        $section->addText('Director of Agriculture', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addText('Sabah, Malaysia', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $fileName = "Inspection_Certificate_{$application->application_id}.docx";
        $tempPath = storage_path("app/{$fileName}");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    function generateConsignment($id)
    {
        $application = ConsignmentApplication::with(['importer', 'exporter', 'consignmentPermits'])
        ->where('application_id', $id)->first();

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
            'borderColor' => '#FFFFFF'
        ]);

        $logoTable->addRow();

        // LEFT LOGO
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/jata-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

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
        $logoTable->addCell(1400, ['valign' => 'center'])->addImage(
            public_path('/asset/sabah-svg.jpg'),
            ['width' => 60, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );

        $section->addTextBreak(1);
        $section->addText('Permit No.:', ['bold' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addTextBreak(1);

        /* ===============================
        PERMIT DETAILS
    =============================== */
        // Name of consignee
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Name of consignee ', ['size' => 11]);
        $textRun->addText(
            str_pad(strtoupper($importer['fullname'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Address
        $textAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textAddress->addText('and address ', ['size' => 11]);
        $fullAddress = ($importer['address_1'] ?? '') .
            ', ' . ($importer['address_2'] ?? '') .
            ', ' . ($importer['postcode'] ?? '') . ' ' . ($importer['district'] ?? '') .
            ', ' . ($importer['state'] ?? '');
        $textAddress->addText(
            str_pad(strtoupper($fullAddress), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Name of consignor
        $textConsignor = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignor->addText('Name of consignor ', ['size' => 11]);
        $textConsignor->addText(
            str_pad(strtoupper($exporter['name'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Consignor address
        $textConsignorAddress = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textConsignorAddress->addText('add address ', ['size' => 11]);
        $textConsignorAddress->addText(
            str_pad(strtoupper($exporter['address'] ?? '-'), 100, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

        $section->addTextBreak(1);

        // Permission text with entry point underlined
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText(
            'Permission is hereby granted to the consignee *soil/rooting compost/growing media/beneficial organisms contained in the Schedule hereto through ',
            ['size' => 11]
        );
        $textRun->addText(
            str_pad(strtoupper($application->entryPoint->entry_name ?? '-'), 50, ' '),
            ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_DOTTED, 'size' => 11]
        );

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
            $consignmentText =
                'Quantity: ' . ($consignment['quantity'] ?? '-') . "\n" .
                'Value: ' . ($consignment['value'] ?? '-') . "\n" .
                'Measure: ' . ($consignment['measure'] ?? '-') . "\n" .
                'Uses: ' . ($consignment['uses'] ?? '-');
        
            $table->addRow();
            $table->addCell(2500)->addText((string) ($item->permit_number ?? '-'));
            $table->addCell(2500)->addText((string) $itemName);
            $table->addCell(3000)->addText((string) ($item->purpose ?? '-'));
            $table->addCell(4000)->addText($consignmentText);
        }
        
        

        $section->addText("Date of Issue: " . now()->format('d/m/Y'), [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $section->addTextBreak(2);
        $section->addText('Director of Agriculture', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
        $section->addText('Sabah, Malaysia', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        $fileName = "Consignment_Certificate_{$application->application_id}.docx";
        $tempPath = storage_path("app/{$fileName}");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
