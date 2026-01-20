<?php

namespace App\Http\Controllers\public\importPermit;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\Country;
use App\Models\ImportPermitLog;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Models\TempAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use App\Notifications\ApplicationNotification;
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

        $exporterId = \DB::table('exporter')->insertGetId([
            'name' => $validated['name'],
            'phone_no' => $validated['phone_no'],
            'address' => $validated['address'],
            'country' => $validated['country'],
            'registered_by' => authUser()['user']['uuid'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // fetch newly created exporter
        $exporter = \DB::table('exporter')->where('id', $exporterId)->first();

        return response()->json($exporter, 201);
    }

    public function deleteExporter($id)
    {
        // Ensure exporter belongs to the logged-in user
        $exporter = \DB::table('exporter')
            ->where('id', $id)
            ->where('registered_by', authUser()['user']['uuid'])
            ->first();

        if (!$exporter) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Exporter not found or unauthorized.',
                ],
                404,
            );
        }

        \DB::table('exporter')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exporter deleted successfully.',
        ]);
    }

    public function getImporters($idno)
    {
        $importers = \DB::table('public_users')->where('no_ic', $idno)->first();

        // If no data found
        if (!$importers) {
            return response()->json(
                [
                    'status' => 'not_found',
                    'message' => 'No importer found for this Identity number.',
                    'data' => [],
                ],
                404,
            );
        }

        // If email not verified
        if (is_null($importers->email_verified_at)) {
            return response()->json([
                'status' => 'not_verified_email',
                'message' => 'User exists but email verification is not completed.',
                'data' => $importers,
            ]);
        }

        // If DOA verified is false
        if ($importers->doa_verified != 1) {
            return response()->json([
                'status' => 'not_verified_doa',
                'message' => 'User exists but DOA verification is not completed.',
                'data' => $importers,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $importers,
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

        $entryp = \DB::table('ip_entry_point')->leftJoin('public_code', 'ip_entry_point.district', '=', 'public_code.cate_code')->where('public_code.cate_name', 'district_entry')->where('ip_entry_point.transport_type', $type)->select('ip_entry_point.id', \DB::raw('CONCAT(public_code.description, " - ", ip_entry_point.entry_name) AS entry_display'))->get();

        return response()->json($entryp);
    }

    public function getConsignmentFromCountry($countryCode)
    {
        $countryCode = strtoupper(trim($countryCode));
        //dd($countryCode);
        $data = IpCondition::whereJsonContains('country', $countryCode)->leftJoin('public_code', 'ip_condition.category', '=', 'public_code.cate_code')->where('public_code.cate_name', 'condition_category')->select('ip_condition.id', \DB::raw('CONCAT(public_code.description, " - ", ip_condition.item_name) AS entry_display'), 'ip_condition.usage')->get();

        return response()->json($data);
    }

    public function getConsignmentUses($id)
    {
        $data = IpCondition::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $data->usage,
        ]);
    }
    public function getAllConsignmentUses()
    {
        $data = IpCondition::get();
        return response()->json([
            'success' => true,
            'data' => $data->usage,
        ]);
    }

    public function saveApplication(Request $request)
    {
        DB::beginTransaction();
        $movedFiles = [];
        $isNewApplication = false;

        try {
            $applicationUuid = $request->input('applicationId');
            $isDraft = $request->boolean('is_draft');

            $exporter = $request->exporterData ? json_decode($request->exporterData, true) : null;

            $importer = $request->importerData ? json_decode($request->importerData, true) : null;

            $permit = $request->permitDetails ? json_decode($request->permitDetails, true) : [];

            // -----------------------------
            // Importer verify logic
            // -----------------------------
            $importer_verify = null;
            if (!$isDraft && isset($permit['applCate'])) {
                $importer_verify = $permit['applCate'] == 0 ? 'Clerk Review In-Progress' : 'wait for company approval';
            }

            // -----------------------------
            // Create / Update Application
            // -----------------------------
            if ($applicationUuid) {
                // Update existing application
                $application = IpApplication::where('application_id', $applicationUuid)->firstOrFail();

                $application->update([
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => Auth::user()->uuid,
                    'exporter_id' => $exporter['id'] ?? null,
                    'importer_id' => $importer['uuid'] ?? null,
                    'importer_detail' => $importer,
                    'status' => $isDraft ? 'Draft' : 'Clerk Review In-Progress',
                    'importer_verify' => $importer_verify,
                ]);

                try {
                    event(new InternalUserAdminEvent($isDraft ? 'Import permit application saved as DRAFT by ' . ($importer['fullname'] ?? 'Unknown Importer') : 'Import permit application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')));
                    event(new InternalUserClerkEvent($isDraft ? 'Import permit application saved as DRAFT by ' . ($importer['fullname'] ?? 'Unknown Importer') : 'Import permit application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')));
                    event(new PublicUserEvent($isDraft ? 'Your Application with id ' . $application->uuid . ' is saved as draft' : 'Your Application with id ' . $application->uuid . ' is submitted', $application->user_id));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing application save: ' . $e->getMessage());
                }
            } else {
                // Create new application
                $status = $isDraft ? 'Draft' : ((int) ($permit['applCate'] ?? 0) === 1 ? 'Awaiting Approval' : 'Clerk Review In-Progress');
                $isNewApplication = true;
                $application = IpApplication::create([
                    'application_id' => Str::uuid(),
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => Auth::user()->uuid,
                    'exporter_id' => $exporter['id'] ?? null,
                    'importer_id' => $importer['uuid'] ?? null,
                    'importer_detail' => $importer,
                    'status' => $status,
                    'importer_verify' => $importer_verify,
                ]);

                // dd($application->importer_detail);

                // event(new ApplicationCreatedInternalUser(
                //     $isDraft
                //         ? 'New import permit application DRAFT created by ' . ($importer['fullname'] ?? 'Unknown Importer')
                //         : 'New import permit application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')
                // ));
                try {
                    event(new InternalUserAdminEvent($isDraft ? 'New import permit application DRAFT created by ' . ($importer['fullname'] ?? 'Unknown Importer') : 'New import permit application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')));
                    event(new InternalUserClerkEvent($isDraft ? 'New import permit application DRAFT created by ' . ($importer['fullname'] ?? 'Unknown Importer') : 'New import permit application submitted by ' . ($importer['fullname'] ?? 'Unknown Importer')));
                    event(new PublicUserEvent($isDraft ? 'Your Application with id ' . $application->application_id . ' is saved as draft' : 'Your Application with id ' . $application->application_id . ' is submitted', $application->user_id));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing application creation: ' . $e->getMessage());
                }

                ApplicationActivityLogger::log(
                    application: $application,
                    event: 'create_application',
                    description: authUser()['user']->fullname . " create application with id {$application->application_id}",
                    properties: [
                        'role' => 'public',
                    ],
                );

                if (!$isDraft) {
                    $notificationController = new NotificationController();

                    $notificationController->sendStatusMessage(
                        $application->importer_detail['fullname'] ?? 'User',
                        'Import Permit',
                        $application->application_id,
                        'will be check by DOA',
                        `Your application has been successfully submitted.`,
                        $application->importer->phone_number ?? '60143290092', // recipient number
                    );
                }
            }

            $appId = $application->id;

            // -----------------------------
            // Sync Consignments
            // -----------------------------
            $existingIds = IpConsignmentPermit::where('application_id', $appId)->pluck('id')->toArray();
            $deletedPermits = $request->input('deleted_item_ids', []);

            if (is_string($deletedPermits)) {
                $deletedPermits = array_filter(explode(',', $deletedPermits));
            }

            if ($deletedPermits) {
                foreach ($deletedPermits as $permitId) {
                    $permitItem = IpConsignmentPermit::with('attachments')->find($permitId);
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

            // -----------------------------
            // Create / Update consignments
            // -----------------------------
            $consignmentArray = [];

            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $data = json_decode($item['data'], true);
                    $permit_id = $data['permit_id'] ?? null;

                    if ($permit_id && in_array($permit_id, $existingIds)) {
                        continue;
                    }

                    $consignment = IpConsignmentPermit::create([
                        'application_id' => $appId,
                        'permit_number' => null,
                        'consignment_detail' => $data,
                        'quantity' => $data['quantity'] ?? 0,
                        'unit_measurement' => $data['measure'] ?? null,
                        'value' => $data['value'] ?? 0,
                        'purpose' => $data['purpose'] ?? null,
                        'status' => 'processing',
                    ]);

                    $consignmentArray[$index] = $consignment->id;
                }
            }

            // -----------------------------
            // Handle Attachments
            // -----------------------------
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $i => $file) {
                    $itemIndex = $request->input('file_item_index')[$i] ?? null;
                    if (!isset($consignmentArray[$itemIndex])) {
                        continue;
                    }

                    $name = uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('import', $name, 'public');
                    $movedFiles[] = $path;

                    IpConsignmentAttachment::create([
                        'permit_id' => $consignmentArray[$itemIndex],
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => "/storage/{$path}",
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // -----------------------------
            // Activity Log
            // -----------------------------
            if ($isDraft) {
                $application->logActivity('Draft', 'Application saved as draft', 'Draft');
            } else {
                $application->logActivity('Submitted', 'Application submitted', 'Submitted');
                $permit['applCate'] == 0 ? $application->logActivity('Clerk Review In-Progress', 'Pending for clerk approval', 'Clerk Review In-Progress') : $application->logActivity('Awaiting Approval', 'Company approval required', 'Awaiting Company Approval');
            }

            DB::commit();

            // -----------------------------
            // Send Notifications
            // -----------------------------
            $users = InternalUser::role(['admin', 'clerk'])->get();
            $notificationUrl = route('viewApplication', $application->application_id);
            Notification::send($users, new ApplicationNotification($isDraft ? ($isNewApplication ? 'New import permit draft created' : 'Import permit draft updated') : ($isNewApplication ? 'New import permit application submitted' : 'Import permit application updated'), Auth::user()->fullname, $notificationUrl));
            $publicUser = auth()->guard('public')->user();
            $publicUser->notify(new ApplicationNotification($isDraft ? 'Your Application with id ' . $application->application_id . ' is saved as draft' : 'Your Application with id ' . $application->application_id . ' is submitted', 'QIS', route('viewApplication', $application->application_id)));

            if ($application->category_application == 1 && !$isDraft) {
                $company = PublicUser::where('uuid', $application['importer_id'])->first();
                try {
                    event(new ApplicationCreatedInternalUser('Import permit application requires company approval for ' . ($company['fullname'] ?? 'Unknown Importer')));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing company approval notification: ' . $e->getMessage());
                }

                $company->notify(new ApplicationNotification('An import permit application requires your approval', 'System', route('viewApplication', $application->application_id)));
            }

            if (!$isDraft) {
                // instantiate the controller

                // $notificationController = new NotificationController();

                // // optionally, pass dynamic data instead of hardcoded
                // $notificationController->sendStatusMessage(
                //     $application->importer_detail['fullname'] ?? 'User',
                //     'Import Permit',
                //     $application->application_id,
                //     'submitted',
                //     'Your application is under review and will be processed shortly.',
                //     $application->importer->phone_number ?? '60143290092' // recipient number
                // );
            }

            return response()->json([
                'status' => 'success',
                'application_id' => $application->application_id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            foreach ($movedFiles as $file) {
                Storage::disk('public')->delete($file);
            }

            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
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
            'message' => 'Old private files moved to public successfully',
        ]);
    }

    public function uploadAttachment(Request $request)
    {
        \Log::info('UPLOAD HIT');
        if (!$request->hasFile('file')) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'No file uploaded',
                ],
                400,
            );
        }

        $file = $request->file('file');

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('permitAttachment', $filename, 'public');

        // Save to database
        $attachment = IpConsignmentAttachment::create([
            'file_name' => $filename,
            'permit_id' => 1,
            'file_path' => '/storage/' . $path,
            'file_type' => $file->getClientMimeType(),
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
            'temp_name' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'temp_path' => $path,
        ]);

        return response()->json([
            'id' => $record->id,
            'original_name' => $record->original_name,
            'temp_name' => $record->temp_name,
            'temp_path' => $record->temp_path,
            'mime_type' => $record->mime_type,
            'size' => $record->size,
        ]);
    }
}
