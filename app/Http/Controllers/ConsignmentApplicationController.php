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
use App\Models\InternalUser;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

            // exporterData = User (because we swapped UI labels, but frontend logic uses 'exporter' for 'Me')
            // importerData = Partner (selected from list)
            $exporterUser = $request->exporterData ? json_decode($request->exporterData, true) : null;
            $importerPartner = $request->importerData ? json_decode($request->importerData, true) : null;


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
                    'user_id' => authUser()['user']->uuid,
                    // exporter = User (uuid)
                    'exporter_id' => $exporterUser['uuid'] ?? null,
                    // importer = Partner (id)
                    'importer_id' => $importerPartner['id'] ?? null,
                    'importer_detail' => $importerPartner,
                    // Status flow: Draft or Application Submitted
                    'status' => $isDraft ? 'Draft' : 'Application Submitted',
                    'importer_verify' => $importer_verify,
                ]);

                try {
                    event(new InternalUserAdminEvent($isDraft ? 'Consignment certificate application saved as DRAFT by ' . ($exporterUser['fullname'] ?? 'Unknown Exporter') : 'Consignment certificate application submitted by ' . ($exporterUser['fullname'] ?? 'Unknown Exporter')));
                    event(new PublicUserEvent($isDraft ? 'Your consignment application with id ' . $application->application_id . ' is saved as draft' : 'Your consignment application with id ' . $application->application_id . ' is submitted', $application->user_id));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing save: ' . $e->getMessage());
                }

            } else {
                // Create new application
                // Status flow: Draft or Application Submitted
                $status = $isDraft ? 'Draft' : 'Application Submitted';

                $isNewApplication = true;
                $application = ConsignmentApplication::create([
                    'application_id' => Str::uuid(),
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => authUser()['user']->uuid,
                    // exporter = User (uuid)
                    'exporter_id' => $exporterUser['uuid'] ?? null,
                    // importer = Partner (id)
                    'importer_id' => $importerPartner['id'] ?? null,
                    'importer_detail' => $importerPartner,
                    'status' => $status,
                    'importer_verify' => $importer_verify,
                ]);

                try {
                    event(new InternalUserAdminEvent($isDraft ? 'Consignment certificate application saved as DRAFT by ' . ($exporterUser['fullname'] ?? 'Unknown Exporter') : 'Consignment certificate application submitted by ' . ($exporterUser['fullname'] ?? 'Unknown Exporter')));
                    event(new PublicUserEvent($isDraft ? 'Your consignment application with id ' . $application->application_id . ' is saved as draft' : 'Your consignment application with id ' . $application->application_id . ' is submitted', $application->user_id));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing save: ' . $e->getMessage());
                }

            }

            $appId = $application->id;

            // -----------------------------
            // Sync Consignments
            // -----------------------------
            $existingIds = ConsignmentPermit::where('application_id', $appId)->pluck('id')->toArray();
            $deletedPermits = $request->input('deleted_item_ids', []);

            if (is_string($deletedPermits)) {
                $deletedPermits = array_filter(explode(',', $deletedPermits));
            }

            if ($deletedPermits) {
                foreach ($deletedPermits as $permitId) {
                    $permitItem = ConsignmentPermit::with('attachments')->find($permitId);
                    if (!$permitItem) {
                        continue;
                    }

                    foreach ($permitItem->attachments as $attachment) {
                        if ($attachment->file_path) {
                            $path = str_replace('/storage/', '', $attachment->file_path);
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }
                        $attachment->delete();
                    }

                    $permitItem->delete();
                }
            }

            // Handle items (consignment permits)
            $consignmentArray = [];
            $items = $request->input('items');

            // dd($items);

            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $data = json_decode($item['data'], true);
                    $permit_id = $data['permit_id'] ?? null;

                    if ($permit_id && in_array($permit_id, $existingIds)) {
                        continue;
                    }

                    $consignment = ConsignmentPermit::create([
                        'application_id' => $appId,
                        'permit_number' => null,
                        'consignment_detail' => $data,
                        'quantity' => $data['quantity'] ?? 0,
                        'unit_measurement' => $data['measure'] ?? null,
                        'value' => $data['value'] ?? 0,
                        'purpose' => $data['purpose'] ?? null,
                        'status' => 'processing',
                        'mygap_myorganic_no' => $data['certificateNo'] ?? null
                    ]);

                    $consignmentArray[$index] = $consignment->id;
                }
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $i => $file) {
                    $itemIndex = $request->input('file_item_index')[$i] ?? null;
                    if (!isset($consignmentArray[$itemIndex])) {
                        continue;
                    }
                    $name = uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('consignment', $name, 'public');
                    $movedFiles[] = $path;

                    ConsignmentAttachment::create([
                        'permit_id' => $consignmentArray[$itemIndex],
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => "/storage/{$path}",
                        'file_type' => $file->getClientOriginalExtension(),
                        'description' => '',

                    ]);
                }
            }

            DB::commit();

            // -----------------------------
            // Send Notifications
            // -----------------------------
            $users = InternalUser::role(['admin', 'clerk'])->get();
            $notificationUrl = url('/view_consignment/' . $application->application_id);
            Notification::send($users, new ApplicationNotification($isDraft ? ($isNewApplication ? 'New consignment certificate draft created' : 'Consignment certificate draft updated') : ($isNewApplication ? 'New consignment certificate application submitted' : 'Consignment certificate application updated'), Auth::user()->fullname ?? 'System', $notificationUrl));
            
            $publicUser = auth()->guard('public')->user();
            if ($publicUser) {
                $publicUser->notify(new ApplicationNotification($isDraft ? 'Your consignment application with id ' . $application->application_id . ' is saved as draft' : 'Your consignment application with id ' . $application->application_id . ' is submitted', 'QIS', $notificationUrl));
            }

            if ($application->category_application == 1 && !$isDraft) {
                // Get the ConsignmentImporter and then the PublicUser who registered it
                $importer = ConsignmentImporter::find($application->importer_id);
                if ($importer && $importer->registered_by) {
                    $company = PublicUser::where('uuid', $importer->registered_by)->first();
                    if ($company) {
                        try {
                            event(new InternalUserAdminEvent('Consignment certificate application requires company approval for ' . ($importer->name ?? 'Unknown Importer')));
                        } catch (\Exception $e) {
                            Log::warning('Pusher connection failed but continuing company approval notification: ' . $e->getMessage());
                        }

                        $company->notify(new ApplicationNotification('A consignment certificate application requires your approval', 'System', $notificationUrl));
                    }
                }
            }

            

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
        \Log::info('Storing Consignment Importer', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone_no' => 'required|string|max:25',
            'address' => 'required|string',
            'country' => 'required|string|max:50',
        ]);

        $user = authUser()['user'];

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $exporter = ConsignmentImporter::create([
            'name' => $validated['name'],
            'phone_no' => $validated['phone_no'],
            'address' => $validated['address'],
            'country' => $validated['country'],
            'registered_by' => $user->uuid,
        ]);

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
    public function deleteApplication($id)
    {
        // Check both internal and public user
        $internalUser = auth()->user();
        $publicUser = auth()->guard('public')->user();
        $user = $internalUser ?? $publicUser;

        try {
            // Security Check - user must be authenticated
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized action. Please log in.'
                ], 401);
            }

            // Find application
            $application = ConsignmentApplication::where('application_id', $id)->firstOrFail();
            
            // Store values before deletion
            $applicationId = $application->application_id;
            $userName = $user->fullname ?? 'Unknown User';

            // Security Check - user must own the application
            if ($application->user_id !== $user->uuid && $application->importer_id !== $user->uuid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized action. You do not own this application.'
                ], 403);
            }

            DB::beginTransaction();

            // 1. Delete Attachments from Storage
            // Get all permits
            $permits = $application->consignmentPermits()->with('attachments')->get();

            foreach ($permits as $permit) {
                foreach ($permit->attachments as $attachment) {
                    // Try to delete file from storage
                    if ($attachment->file_path) {
                        $path = str_replace('/storage/', '', $attachment->file_path);
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }
                    // Delete attachment record
                    $attachment->delete();
                }
                // Delete permit record
                $permit->delete();
            }

            // 2. Delete Application Record
            $application->delete();

            DB::commit();

            // Sends Notifications for deletion
            $notificationUrl = url('/view_consignment/' . $applicationId);
            
            // Notify internal users (admins/clerks)
            try {
                $users = InternalUser::role(['admin', 'clerk'])->get();
                Notification::send($users, new ApplicationNotification('Consignment certificate application deleted by ' . $userName, $userName, $notificationUrl));
            } catch (\Exception $e) {
                Log::warning('Failed to send notification to internal users: ' . $e->getMessage());
            }
            
            // Notify the public user who deleted the application
            if ($publicUser) {
                try {
                    $publicUser->notify(new ApplicationNotification('Your consignment application with id ' . $applicationId . ' has been successfully deleted', 'QIS', $notificationUrl));
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification to public user: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Application deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete application: ' . $e->getMessage()
            ], 500);
        }
    }
}

