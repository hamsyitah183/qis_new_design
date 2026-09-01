<?php

namespace App\Http\Controllers\public\importPermit;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\ConsignmentImporter;
use App\Models\Country;
use App\Models\DocumentRequirement;
use App\Models\ImportPermitLog;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpApplicationAttachment;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Models\Exporter;
use App\Models\TempAttachment;
use App\Models\UserAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Artisan;

// use App\Notifications\ApplicationNotification;
class PermitApplicationController extends Controller
{
    /**
     * Show the permit application page (for self / regular users).
     */
    public function show()
    {
        Artisan::call('bayupay:check-pending');

        $blockView = $this->checkDocumentStatusAndReturnView();
        if ($blockView) {
            return $blockView;
        }

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        
        $ipDocuments = DocumentRequirement::forModule('import')
            ->orderBy('name')
            ->get();
            
        return view('pages.public.apply_permit', compact('pubmeasure', 'pubpurpose', 'country', 'ipDocuments'));
    }

    /**
     * Show the assigned permit application page (for others / company).
     */
    public function showassign()
    {
        $blockView = $this->checkDocumentStatusAndReturnView();
        if ($blockView) {
            return $blockView;
        }

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        
        $ipDocuments = DocumentRequirement::forModule('import')
            ->orderBy('name')
            ->get();
            
        return view('pages.public.assigned_apply_permit', compact('pubmeasure', 'pubpurpose', 'country', 'ipDocuments'));
    }

    private function checkDocumentStatusAndReturnView()
    {
        $user = authUser()['user'];

        // ✅ If the user is already DOA-verified, allow access without blocking
        if ($user->doa_verified == 1) {
            return null;
        }

        // Not verified – check required documents
        $requirements = DocumentRequirement::where('module', 'user')
            ->where('is_required', true)
            ->where('is_active', true)
            ->get();

        $attachments = UserAttachment::where('user_id', $user->uuid)
            ->get()
            ->keyBy('document_type');

        $docStatus = [];
        foreach ($requirements as $req) {
            $attachment = $attachments->get($req->name);
            if ($attachment) {
                if (!$attachment->is_read) {
                    $status = 'pending';
                } else {
                    $isExpired = $req->requires_expiry && $attachment->valid_until && now()->greaterThan($attachment->valid_until);
                    $status = $isExpired ? 'expired' : 'uploaded';
                }
            } else {
                $status = 'missing';
            }
            $docStatus[] = [
                'requirement' => $req,
                'attachment' => $attachment,
                'status' => $status,
            ];
        }

        $anyMissing = collect($docStatus)->contains(fn($item) => $item['status'] === 'missing');
        $anyExpired = collect($docStatus)->contains(fn($item) => $item['status'] === 'expired');

        if ($anyMissing || $anyExpired) {
            return view('pages.public.wait_for_verified', compact('docStatus'));
        }

        return null;
    }

    
    public function storeExporter(Request $request)
    {
        // dd($request['id']);
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone_no' => 'required|string|max:25',
            'address' => 'required|string',
            'country' => 'required|string|max:50',
        ]);

        if (!$request['id']) {
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
        } else {
            $exporter = Exporter::with(['countryInfo'])->find($request['id']);

            $exporter->name = $validated['name'];
            $exporter->phone_no = $validated['phone_no'];
            $exporter->address = $validated['address'];
            $exporter->country = $validated['country'];
            $exporter->save();
        }

        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->event('add exporter')
            ->causedBy(authUser()['user'])
            ->performedOn(authUser()['user'])
            ->withProperties([
                'exporter' => $exporter,
            ])
            ->log(authUser()['user']['fullname'] . ' has added an exporter (' . $exporter->name . ')');

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

        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->event('delete exporter')
            ->causedBy(authUser()['user'])
            ->performedOn(authUser()['user'])
            ->withProperties([
                'exporter_id' => $id
            ])
            ->log(authUser()['user']['fullname'] . ' has deleted an exporter');

        return response()->json([
            'success' => true,
            'message' => 'Exporter deleted successfully.',
        ]);
    }

    public function getImporters($idno)
    {
        $authNoIc = authUser()['user']->no_ic ?? null;

        if ($authNoIc && $authNoIc === $idno) {
            return response()->json(
                [
                    'status' => 'not_found',
                    'message' => 'Enter another user Identity Number',
                    'data' => [],
                ],
                404,
            );
        }

        $importers = \DB::table('public_users')
            ->where('no_ic', $idno)
            ->first();

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

    public function getExporters(Request $request)
    {
        $query = \DB::table('exporter')
            ->leftJoin('country', 'exporter.country', '=', 'country.code')
            ->where('registered_by', auth('public')->id());

        if ($request->filled('name')) {
            $query->where('exporter.name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('country')) {
            $query->where('exporter.country', $request->input('country'));
        }

        $exporters = $query
            ->select(
                'exporter.id as id',
                'exporter.name as name',
                'exporter.phone_no as phone_no',
                'exporter.address as address',
                'exporter.country as ccode',
                'country.name as country'
            )
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($exporters);
    }

    public function getConsignmentImporters(Request $request)
    {
        $query = ConsignmentImporter::with('countryInfo');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        $importers = $query->get();

        return response()->json($importers);
    }


    public function showExportersPage()
    {
        $exporters = Exporter::with('countryInfo')
            ->where('registered_by', auth('public')->id())
            ->orderBy('name', 'asc')
            ->get();

        return view('pages.public.exporters.index', compact('exporters'));
    }

    public function getEntryPoint(Request $request)
    {
        $type = $request->query('type');

        $entryp = \DB::table('ip_entry_point')
            ->leftJoin('public_code', 'ip_entry_point.district', '=', 'public_code.cate_code')
            ->where('public_code.cate_name', 'district_entry')
            ->where('public_code.is_del', false)
            ->where('ip_entry_point.is_del', false)
            ->where('ip_entry_point.transport_type', $type)
            ->select('ip_entry_point.id', \DB::raw('CONCAT(public_code.description, " - ", ip_entry_point.entry_name) AS entry_display'))
            ->get();

        return response()->json($entryp);
    }

    public function getConsignmentFromCountry($countryCode)
    {
        $countryCode = strtoupper(trim($countryCode));
        //dd($countryCode);
        $data = IpCondition::whereJsonContains('country', $countryCode)->leftJoin('public_code', 'ip_condition.category', '=', 'public_code.cate_code')
        ->where('public_code.cate_name', 'condition_category')->select('ip_condition.id', \DB::raw('CONCAT(ip_condition.item_name) AS entry_display'), 
        'ip_condition.usage', 
        'ip_condition.another_name'
        )->get();

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
            $lang = $request->input('lang', 'en');
            app()->setLocale($lang); // Set locale for __() translations

            $exporter = $request->exporterData ? json_decode($request->exporterData, true) : null;
            $importer = $request->importerData ? json_decode($request->importerData, true) : null;
            $permit = $request->permitDetails ? json_decode($request->permitDetails, true) : [];

            // Importer verify logic
            $importer_verify = null;
            if (!$isDraft && isset($permit['applCate'])) {
                $importer_verify = $permit['applCate'] == 0
                    ? 'Clerk Review In-Progress'
                    : 'Wait for Representative Approval';
            }

            // Create / Update Application
            if ($applicationUuid) {
                $application = IpApplication::where('application_id', $applicationUuid)->firstOrFail();

                $application->update([
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => authUser()['user']['uuid'] ?? null,
                    'exporter_id' => $exporter['id'] ?? null,
                    'importer_id' => $importer['uuid'] ?? null,
                    'importer_detail' => $importer,
                    'status' => $isDraft ? 'Draft' : 'Clerk Review In-Progress',
                    'importer_verify' => $importer_verify,
                ]);

                activity()
                    ->tap(fn($activity) => $activity->log_name = 'user_activity')
                    ->event($isDraft ? 'update draft application' : 'update import permit application')
                    ->causedBy(authUser()['user'])
                    ->performedOn($application)
                    ->withProperties(['application' => $application])
                    ->log(authUser()['user']['fullname'] . ' updated application (ID: ' . $application->application_id . ')');
            } else {
                $status = $isDraft
                    ? 'Draft'
                    : ((int) ($permit['applCate'] ?? 0) === 1
                        ? 'Wait for Representative Approval'
                        : 'Clerk Review In-Progress');

                $isNewApplication = true;

                $application = IpApplication::create([
                    'application_id' => 'IPO' . now()->format('ymd') . random_int(1000, 9999),
                    'eta' => $permit['eta'] ?? null,
                    'transport_type' => $permit['tranType'] ?? null,
                    'entry_point' => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id' => authUser()['user']['uuid'] ?? null,
                    'exporter_id' => $exporter['id'] ?? null,
                    'importer_id' => $importer['uuid'] ?? null,
                    'importer_detail' => $importer,
                    'status' => $status,
                    'importer_verify' => $importer_verify,
                ]);

                activity()
                    ->tap(fn($activity) => $activity->log_name = 'user_activity')
                    ->event($isDraft ? 'create draft application' : 'create import permit application')
                    ->causedBy(authUser()['user'])
                    ->performedOn($application)
                    ->withProperties(['application' => $application])
                    ->log(authUser()['user']['fullname'] . ' created application (ID: ' . $application->application_id . ')');
            }

            $appId = $application->id;

            // Sync consignments (unchanged)
            $existingIds = IpConsignmentPermit::where('application_id', $appId)->pluck('id')->toArray();
            $deletedPermits = $request->input('deleted_item_ids', []);

            if (is_string($deletedPermits)) {
                $deletedPermits = array_filter(explode(',', $deletedPermits));
            }

            foreach ($deletedPermits as $permitId) {
                $permitItem = IpConsignmentPermit::with('attachments')->find($permitId);
                if (!$permitItem) continue;

                foreach ($permitItem->attachments as $attachment) {
                    if ($attachment->file_path) {
                        $path = str_replace('/storage/', '', $attachment->file_path);
                        Storage::disk('public')->delete($path);
                    }
                    $attachment->delete();
                }

                $permitItem->delete();
            }

            $consignmentArray = [];

            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $data = json_decode($item['data'], true);
                    $permit_id = $data['permit_id'] ?? null;

                    if ($permit_id && in_array($permit_id, $existingIds)) continue;

                    $consignment = IpConsignmentPermit::create([
                        'application_id' => $appId,
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

            // Attachments (unchanged)
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $i => $file) {
                    $itemIndex = $request->input('file_item_index')[$i] ?? null;
                    if (!isset($consignmentArray[$itemIndex])) continue;

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

            if ($request->hasFile('application_files')) {
                $documentTypes = $request->input('application_files_document_type', []);
                $descriptions  = $request->input('application_files_description', []);

                foreach ($request->file('application_files') as $i => $file) {
                    $name = uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('import_applications', $name, 'public');
                    $movedFiles[] = $path;

                    IpApplicationAttachment::create([
                        'application_id' => $application->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => "/storage/{$path}",
                        'file_type' => $file->getClientOriginalExtension(),
                        'description'    => $descriptions[$i] ?? ($documentTypes[$i] ?? null),
                    ]);
                }
            }

            DB::commit();

            // --------------------------------------------
            // BILINGUAL NOTIFICATIONS (SMS + In-app)
            // --------------------------------------------

            // 1. SMS Notifications (using __() with current locale)
            if (!$isDraft) {
                $notificationController = new NotificationController();

                if ($application->category_application == 1) {
                    // Importer (company) – needs approval
                    $notificationController->sendStatusMessage(
                        $application->importer['fullname'] ?? 'User',
                        'Import Permit',
                        $application->application_id,
                        'submitted',
                        __('notifications.sms_importer_approval'),
                        $application->importer->phone_number ?? '60143290092'
                    );

                    // Applicant – waiting for approval
                    $notificationController->sendStatusMessage(
                        $application->user['fullname'] ?? 'User',
                        'Import Permit',
                        $application->application_id,
                        'submitted',
                        __('notifications.sms_applicant_waiting'),
                        $application->user->phone_number ?? '60143290092'
                    );
                } else {
                    $notificationController->sendStatusMessage(
                        $application->importer['fullname'] ?? 'User',
                        'Import Permit',
                        $application->application_id,
                        'submitted',
                        __('notifications.sms_applicant_success'),
                        $application->importer->phone_number ?? '60143290092'
                    );
                }
            }

            // 2. In-app Notifications (store both languages)
            $internalNotificationUrl = route('viewApplication', $application->application_id);
            $publicNotificationUrl = route('public.showallapplicationlist'); // Public user dashboard

            // Build bilingual messages using translation keys with placeholders
            // We need both versions, so we temporarily switch locale to get each.
            $originalLocale = app()->getLocale();

            // Internal users (admins/clerks)
            $internalKey = $isDraft
                ? ($isNewApplication ? 'notifications.internal_draft_created' : 'notifications.internal_draft_updated')
                : ($isNewApplication ? 'notifications.internal_submit_created' : 'notifications.internal_submit_updated');

            // Get English version
            app()->setLocale('en');
            $internalEn = __($internalKey);
            // Get BM version
            app()->setLocale('bm');
            $internalBm = __($internalKey);

            // Restore original locale for the rest of the request
            app()->setLocale($originalLocale);

            $internalUsers = InternalUser::permission('approve application')->get();
            if (!$isDraft) {
                Notification::send($internalUsers, new \App\Notifications\ApplicationSubmittedNotification(
                    $internalEn,
                    $internalBm,
                    auth()->guard('public')->user()?->fullname ?? auth()->user()?->fullname ?? 'System',
                    $internalNotificationUrl,
                    $application->application_id
                ));
            } else {
                Notification::send($internalUsers, new ApplicationNotification(
                    $internalEn,
                    $internalBm,
                    auth()->guard('public')->user()?->fullname ?? auth()->user()?->fullname ?? 'System',
                    $internalNotificationUrl
                ));
            }

            // Public applicant
            $publicUser = auth()->guard('public')->user();
            if ($publicUser) {
                $publicKey = $isDraft ? 'notifications.public_draft' : 'notifications.public_submit';
                $params = ['id' => $application->application_id];

                app()->setLocale('en');
                $publicEn = __($publicKey, $params);
                app()->setLocale('bm');
                $publicBm = __($publicKey, $params);
                app()->setLocale($originalLocale);

                $publicUser->notify(new ApplicationNotification(
                    $publicEn,
                    $publicBm,
                    'QIS',
                    $publicNotificationUrl
                ));
            }

            return response()->json([
                'status' => 'success',
                'application_id' => $application->application_id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Permit save error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());

            foreach ($movedFiles as $file) {
                Storage::disk('public')->delete($file);
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
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

    public function reapply($id, Request $request)
    {
        $permit = IpConsignmentPermit::with(['application', 'attachments'])->findOrFail($id);
        $application = $permit->application;

        // 1. Remove old attachments (storage + DB)
        foreach ($permit->attachments as $attachment) {
            if ($attachment->file_path) {
                $storagePath = str_replace('/storage/', '', $attachment->file_path);
                Storage::disk('public')->delete($storagePath);
            }
            $attachment->delete();
        }

        // 2. Get new item data
        $item = $request->items[0] ?? null;
        if (!$item || !isset($item['data'])) {
            return response()->json(['message' => 'Invalid item data'], 422);
        }
        $data = json_decode($item['data'], true);

        // 3. Update permit fields
        $permit->update([
            'consignment_detail' => $data,
            'quantity' => $data['quantity'] ?? $permit->quantity,
            'unit_measurement' => $data['measure'] ?? $permit->unit_measurement,
            'value' => $data['value'] ?? $permit->value,
            'purpose' => $data['purpose'] ?? $permit->purpose,
            'status' => 'reapplied',
        ]);

        // 4. Save new attachments
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $name = uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('import', $name, 'public');

                IpConsignmentAttachment::create([
                    'permit_id' => $permit->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => "/storage/{$path}",
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        // 5. Update application status
        $application->status = 'Clerk Verified';
        $application->save();

        // 6. Log the re‑application on the permit
        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->event('reapply')
            ->causedBy(authUser()['user'])
            ->performedOn($permit)
            ->withProperties([
                'stage' => 'consignment_reapply',
                'permit_id' => $permit->id,
            ])
            ->log(authUser()['user']['fullname'] . ' reapplied for permit #' . $permit->permit_number);

        // 7. Also log on the application level (keeps timeline consistent)
        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->event('reapply')
            ->causedBy(authUser()['user'])
            ->performedOn($application)
            ->withProperties([
                'stage' => 'consignment_reapply',
                'permit_id' => $permit->id,
            ])
            ->log('Permit #' . $permit->permit_number . ' reapplied for application ' . $application->application_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Permit updated and files uploaded successfully',
        ]);
    }
}
