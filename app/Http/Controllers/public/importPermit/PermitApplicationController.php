<?php

namespace App\Http\Controllers\public\importPermit;

use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\IpApplication;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use App\Models\TempAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PermitApplicationController extends Controller
{
    public function show()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();
        return view('pages.public.apply_permit', compact('pubmeasure', 'pubpurpose', 'country')); // , compact('')
    }

    public function showassign()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();
        return view('pages.public.assigned_apply_permit', compact('pubmeasure', 'pubpurpose', 'country')); // , compact('')
    }

    public function storeExporter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone_no' => 'required|string|max:25',
            'address' => 'required|string',
            'country' => 'required|string|max:50',
        ]);

        \DB::table('exporter')->insert([
            'name' => $validated['name'],
            'phone_no' => $validated['phone_no'],
            'address' => $validated['address'],
            'country' => $validated['country'],
            // 'registered_by' => auth()->id() ?? 2, // fallback if not logged in
            'registered_by' => authUser()['user']['uuid'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getImporters($idno){
        $importers = \DB::table('public_users')
                ->where('no_ic', $idno)
                ->first();
        
        // If no data found
        if (!$importers) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'No importer found for this Identity number.',
                'data'    => []
            ], 404);
        }

        // If email not verified
        if (is_null($importers->email_verified_at)) {
            return response()->json([
                'status'  => 'not_verified_email',
                'message' => 'User exists but email verification is not completed.',
                'data'    => $importers
            ]);
        }

        // If DOA verified is false
        if ($importers->doa_verified != 1) {
            return response()->json([
                'status'  => 'not_verified_doa',
                'message' => 'User exists but DOA verification is not completed.',
                'data'    => $importers
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $importers
        ]);
    }

    public function getExporters()
    {
        $exporters = \DB::table('exporter')
            ->leftJoin('country', 'exporter.country', '=', 'country.code')
            ->where('registered_by', auth('public')->id())
            ->select('exporter.id as id', 'exporter.name as name', 'exporter.phone_no as phone_no', 'exporter.address as address', 'exporter.country as ccode', 'country.name as country')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($exporters);
    }

    public function getEntryPoint(Request $request)
    {
        $type = $request->query('type');

        $entryp = \DB::table('ip_entry_point')
        ->leftJoin('public_code', 'ip_entry_point.district', '=', 'public_code.cate_code')
        ->where('public_code.cate_name','district_entry')
        ->where('ip_entry_point.transport_type', $type)
        ->select('ip_entry_point.id', \DB::raw('CONCAT(public_code.description, " - ", ip_entry_point.entry_name) AS entry_display'))
        ->get();
        
        return response()->json($entryp);
    }

    public function getConsignmentFromCountry($countryCode)
    {
        
        $countryCode = strtoupper(trim($countryCode));
        //dd($countryCode);
        $data = IpCondition::whereJsonContains('country', $countryCode)
        ->leftJoin('public_code', 'ip_condition.category', '=', 'public_code.cate_code')
        ->where('public_code.cate_name','condition_category')
        ->select(
            'ip_condition.id',
            \DB::raw('CONCAT(public_code.description, " - ", ip_condition.item_name) AS entry_display'),
            'ip_condition.usage'            
        )
        ->get();

        return response()->json($data);
    }

    public function getConsignmentUses($id)
    {
        $data = IpCondition::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $data->usage
        ]);
    }

    public function saveApplication(Request $request)
    {
        // dd($request->all(), $request->file());
        // Decode JSON strings into PHP arrays
        $exporter     = json_decode($request->exporterData, true);
        $importer     = json_decode($request->importerData, true);
        $permit       = json_decode($request->permitDetails, true);
        $items        = json_decode($request->items, true);
        // $attachment   = json_decode($request->attached, true);
        //dd($attachment);

        
        // Step 2: Create IpApplication
        $application = IpApplication::create([
            'application_id'      => Str::uuid(),
            'eta'                 => $permit['eta'],
            'transport_type'      => $permit['tranType'],
            'entry_point'         => $permit['entrypoint'],
            'user_id'             => Auth::id(),                 // submitted by logged-in user
            'exporter_id'         => $exporter['id'],// need to change later
            'importer_id'         => $importer['id'],
            'importer_detail'     => json_encode($importer), // JSON stored
            'category_application'=> $permit['applCate'],
            'importer_verify'     => false,
            'date_importer_verify' => null,
        ]);

        $appId = $application->id;
        $jsencode = json_encode($items);
        // Step 4: Create IpConsignmentPermit
        foreach($items as $item){
            $consignment = IpConsignmentPermit::create([
                'application_id'     => $appId, // FK
                'permit_number'      => null,   // not yet issued
                'consignment_detail' => $jsencode, // JSON
                'quantity'           => $item['quantity'],
                'unit_measurement'   => $item['measure'],
                'value'              => $item['value'],
                'purpose'            => $item['purpose'],
            ]);

            // Save attachments (temp -> permanent)
            if (!empty($item['temp'])) {

                foreach ($item['temp'] as $tempatt) {

                    // Move from temp/ to public/permitAttachment/
                    Storage::move(
                        "public/" . $tempatt['temp_path'],                 // FROM: storage/app/public/temp/...
                        "public/permitAttachment/" . $tempatt['temp_name'] // TO: storage/app/public/permitAttachment/...
                    );

                    // Save final attachment record
                    IpConsignmentAttachment::create([
                        'permit_id' => $consignment->id,
                        'file_name'      => $tempatt['original_name'],
                        'file_path'      => "/storage/temp/" . $tempatt['temp_name'],
                        'file_type'      => $tempatt['mime_type']
                    ]);

                    // Delete temp DB entry
                    TempAttachment::where('id', $tempatt['id'])->delete();
                }
            }
        }

        // Step 5: Respond to frontend
        return response()->json([
            'status' => 'success',
            'message' => $request->all(),//,
            
        ]);
    }

    public function uploadAttachment(Request $request)
    {
        \Log::info("UPLOAD HIT");
        if (!$request->hasFile('file')) {
            return response()->json([
                'status' => 'error',
                'message' => 'No file uploaded'
            ], 400);
        }

        $file = $request->file('file');

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('permitAttachment', $filename, 'public');

        // Save to database
        $attachment = IpConsignmentAttachment::create([
            'file_name' => $filename,
            'permit_id' => 1,
            'file_path' => "/storage/" . $path,
            'file_type' => $file->getClientMimeType()
        ]);

        return response()->json([
            'status' => 'success',
            'file_id' => $attachment->id,
            'file_name' => $attachment->file_name,
            'file_url' => $attachment->file_path,
            'file_type' => $attachment->file_type,
        ]);
    }

    public function tempUpload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['status' => 'error'], 400);
        }

        $file = $request->file('file');

        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('temp', $filename, 'public'); // temp folder

        $record = TempAttachment::create([
            'temp_name'     => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getClientMimeType(),
            'size'          => $file->getSize(),
            'temp_path'     => $path
        ]);

        return response()->json([
            'id'            => $record->id,
            'original_name' => $record->original_name,
            'temp_name'     => $record->temp_name,
            'temp_path'     => $record->temp_path,
            'mime_type'     => $record->mime_type,
            'size'          => $record->size,
        ]);
    }


}
