<?php

namespace App\Http\Controllers;

use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Models\ConsignmentApplication;
use App\Models\ConsignmentPermit;
use App\Models\ConsignmentAttachment;
use App\Models\ConsignmentCondition;
use App\Models\ConsignmentImporter;
use App\Models\PublicCode;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConsignmentApplicationController extends Controller
{
    public function getView()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.consignmentapp', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    public function getViewOther()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.consignmentappOther', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    public function getConsignmentImporters()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(
                [
                    'message' => 'Unauthenticated.',
                ],
                401,
            );
        }

        $importers = ConsignmentImporter::with('countryInfo')->where('registered_by', $user->uuid)->get();

        return response()->json([
            'success' => true,
            'data' => $importers,
        ]);
    }

    public function getConsignmentFromCountry($countryCode)
    {
        $data = ConsignmentCondition::whereJsonContains('country', $countryCode)->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function saveApplication(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        $movedFiles = [];
        $isNewApplication = false;

        try {
            $applicationUuid = $request->input('applicationId');
            $isDraft = $request->boolean('is_draft');

            $importer = $request->exporterData ? json_decode($request->exporterData, true) : null;

            $exporter = $request->importerData ? json_decode($request->importerData, true) : null;

            $permit = $request->permitDetails ? json_decode($request->permitDetails, true) : [];

            // dd('exporter', $exporter,  'importer', $importer, 'permit', $permit);

            // Importer verify logic
            $importer_verify = null;
            if (!$isDraft && isset($permit['applCate'])) {
                $importer_verify = $permit['applCate'] == 0 ? 'Clerk Review In-Progress' : 'wait for company approval';
            }

            // Create / Update Application
            if ($applicationUuid) {
                // Update existing application
                $application = ConsignmentApplication::where('application_id', $applicationUuid)->firstOrFail();

                $application->update([
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => Auth::user()->uuid,
                    'exporter_id' => $exporter['uuid'] ?? null,
                    'importer_id' => $importer['id'] ?? null,
                    'importer_detail' => $importer,
                    'status' => $isDraft ? 'Draft' : 'Submitted',
                    'importer_verify' => $importer_verify,
                ]);

                event(new InternalUserAdminEvent($isDraft ? 'Consignment certificate application saved as DRAFT by ' . ($importer['fullname'] ?? 'Unknown Importer') : 'Consignment certificate application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')));
                event(new PublicUserEvent($isDraft ? 'Your consignment application with id ' . $application->application_id . ' is saved as draft' : 'Your consignment application with id ' . $application->application_id . ' is submitted', $application->user_id));
            } else {
                // Create new application
                $status = $isDraft ? 'Draft' : ((int) ($permit['applCate'] ?? 0) === 1 ? 'Awaiting Approval' : 'Submitted');

                $isNewApplication = true;
                $application = ConsignmentApplication::create([
                    'application_id' => Str::uuid(),
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => Auth::user()->uuid,
                    'exporter_id' => $exporter['uuid'] ?? null,
                    'importer_id' => $importer['id'] ?? null,
                    'importer_detail' => $importer,
                    'status' => $status,
                    'importer_verify' => $importer_verify,
                ]);

                event(new InternalUserAdminEvent($isDraft ? 'Consignment certificate application saved as DRAFT by ' . ($importer['fullname'] ?? 'Unknown Importer') : 'Consignment certificate application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')));
                event(new PublicUserEvent($isDraft ? 'Your consignment application with id ' . $application->application_id . ' is saved as draft' : 'Your consignment application with id ' . $application->application_id . ' is submitted', $application->user_id));
            }

            // Handle items (consignment permits)
            $items = $request->input('items');

          

            if ($items && is_array($items)) {
                foreach ($items as $index => $itemData) {
                    $data = isset($itemData['data']) ? json_decode($itemData['data'], true) : $itemData;

                    //   dd($data);

                    $permit = ConsignmentPermit::updateOrCreate(
                        [
                            'application_id' => $application->id,
                            'consignment_detail' => json_encode($data),
                        ],
                        [
                            'quantity' => $data['quantity'] ?? 0,
                            'unit_measurement' => $data['measure'] ?? null,
                            'value' => $data['value'] ?? 0,
                            'purpose' => $data['purpose'] ?? null,
                            'status' => 'submitted',
                        ],
                    );

                    // Handle file attachments
                    if ($request->hasFile('files')) {
                        $fileIndices = $request->input('file_item_index', []);
                        foreach ($fileIndices as $fileIndex => $itemIndex) {
                            if ((int) $itemIndex === $index) {
                                $file = $request->file('files')[$fileIndex];
                                $path = $file->store('consignment_attachments');

                                ConsignmentAttachment::create([
                                    'permit_id' => $permit->id,
                                    'file_name' => $file->getClientOriginalName(),
                                    'file_path' => $path,
                                    'file_type' => $file->extension(),
                                    'description' => $data['description'] ?? null,
                                ]);

                                $movedFiles[] = $path;
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $isDraft ? 'Draft saved successfully' : 'Application submitted successfully',
                'id' => $application->application_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete moved files
            foreach ($movedFiles as $file) {
                \Storage::delete($file);
            }

            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Failed to save application: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    function storeConsignmentImporter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone_no' => 'required|string|max:25',
            'address' => 'required|string',
            'country' => 'required|string|max:50',
        ]);

        $exporterId = \DB::table('consignment_importers')->insertGetId([
            'name' => $validated['name'],
            'phone_no' => $validated['phone_no'],
            'address' => $validated['address'],
            'country' => $validated['country'],
            'registered_by' => authUser()['user']['uuid'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // fetch newly created exporter
        $exporter = \DB::table('consignment_importers')->where('id', $exporterId)->first();

        return response()->json($exporter, 201);
    }

    public function viewapplication($uuid)
    {
        $application = ConsignmentApplication::with([
            'user', // submitted by
            'importer', // importer user
            'exporter', // exporter record
            'entryPoint.districtCode',
        ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        $itemId = $application->id;

        // dd($application->consignmentPermits);

        // $consignment = IpConsignmentPermit::with(['unit', 'purposeCode'])
        //     ->where('application_id', $itemId)
        //     ->get();
        $consignment = [];

        // dd($consignment);
        // dd($application->exporter);

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();

        return view('pages.public.view_consignment_application', [
            'application' => $application,
            'consignment' => $consignment,
            'pubmeasure' => $pubmeasure,
            'pubpurpose' => $pubpurpose,
            'country' => $country,
            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
}
