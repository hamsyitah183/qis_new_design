<?php

namespace App\Http\Controllers\public\importPermit;

use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\ImportPermitLog;
use App\Models\IpApplication;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use App\Models\TempAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function getImporters($idno)
    {
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
            ->where('public_code.cate_name', 'district_entry')
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
            ->where('public_code.cate_name', 'condition_category')
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
        DB::beginTransaction();
        $movedFiles = [];

        try {
            $exporter = json_decode($request->exporterData, true);
            $importer = json_decode($request->importerData, true);
            $permit   = json_decode($request->permitDetails, true);

            if ($permit['applCate'] == 0) {
                $importer_verify = 'pending';
            } else {
                $importer_verify = 'wait for company approval';
            }

            // Step 1: Create application
            $application = IpApplication::create([
                'application_id'       => Str::uuid(),
                'eta'                  => $permit['eta'],
                'transport_type'       => $permit['tranType'],
                'entry_point'          => $permit['entrypoint'],
                'user_id'              => Auth::user()->uuid,
                'exporter_id'          => $exporter['id'],
                'importer_id'          => $importer['uuid'],
                'importer_detail'      => json_encode($importer),
                'category_application' => $permit['applCate'],
                'status' => 'Pending',
                'importer_verify' => $importer_verify,
            ]);

            $appId = $application->id;

            // Map of item index => consignment ID
            $consignmentArray = [];

            // Step 2: Create consignments
            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $itemData = json_decode($item['data'], true);

                    $consignment = IpConsignmentPermit::create([
                        'application_id'     => $appId,
                        'permit_number'      => null,
                        'consignment_detail' => json_encode($itemData),
                        'quantity'           => $itemData['quantity'],
                        'unit_measurement'   => $itemData['measure'],
                        'value'              => $itemData['value'],
                        'purpose'            => $itemData['purpose'],
                    ]);

                    $consignmentArray[$index] = $consignment->id;
                }
            }

            // Step 3: Handle files
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $i => $file) {
                    $itemIndex = $request->input('file_item_index')[$i] ?? null;
                    if ($itemIndex === null) continue;

                    $consignmentId = $consignmentArray[$itemIndex] ?? null;
                    if (!$consignmentId) continue;

                    // Generate unique filename
                    $name = uniqid() . "_" . $file->getClientOriginalName();

                    // Store in public disk
                    $path = $file->storeAs('import', $name, 'public');
                    $movedFiles[] = $path;

                    // Store relative path in DB
                    $relativePath = "/storage/import/$name";

                    IpConsignmentAttachment::create([
                        'permit_id' => $consignmentId,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $relativePath, // store relative path
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            $application->logActivity(
                action: 'Submitted',
                remark: 'Application Submitted',
                status: 'Submitted'
            );

            if ($permit['applCate'] == 0) {
                $application->logActivity(
                    action: 'Pending',
                    remark: 'Application Pending',
                    status: 'Pending'
                );
            } else {
                $application->logActivity(
                    action: 'Waiting Approval',
                    remark: 'Wait for approval',
                    status: 'Wait for Company Approved'
                );
            }


            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Application saved successfully',
                'application_id' => $application->application_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete any moved files if something failed
            foreach ($movedFiles as $file) {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save application: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optional: Move old files from private/public/import to public/import
     */
    public function moveOldPrivateFiles()
    {
        $oldFiles = Storage::disk('local')->files('private/public/import');

        foreach ($oldFiles as $file) {
            $filename = basename($file);

            // Move file to public disk
            Storage::disk('public')->putFileAs('import', storage_path("app/$file"), $filename);

            // Delete old file
            Storage::disk('local')->delete($file);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Old private files moved to public successfully'
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

        $filename = time() . '_' . $file->getClientOriginalName();
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
