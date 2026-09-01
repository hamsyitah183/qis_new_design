<?php

namespace App\Http\Controllers;

use App\Models\InspectionApplication;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\PublicCode;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Models\DocumentRequirement;
use App\Models\ImportPermitLog;
use App\Models\InspectionAttachment;
use App\Models\InspectionItem;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\InternalUser;
use App\Models\PublicUser;
use App\Models\TempAttachment;
use App\Models\UserAttachment;
use App\Notifications\ApplicationNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Activitylog\Models\Activity;

class InspectionController extends Controller
{
    private function getFilteredInspectionQuery(Request $request)
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = InspectionApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog.causer', 'inspectionItems']);

        // Filter for public users
        if ($type === 'public') {
            $query->where(function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid)->orWhere('importer_id', $userUuid);
            });
        }

        // Apply filters from request
        if ($request->has('status') && $request->status != '') {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by exporter ID
        if ($request->has('exporter_id') && $request->exporter_id != '') {
            $exporterIds = explode(',', $request->exporter_id);
            $query->whereIn('exporter_id', $exporterIds);
        }

        // Filter by importer ID
        if ($request->has('importer_id') && $request->importer_id != '') {
            $importerIds = explode(',', $request->importer_id);
            $query->whereIn('importer_id', $importerIds);
        }

        // Filter by public user UUID (for internal)
        if ($type === 'internal' && $request->has('public_user_uuid') && $request->public_user_uuid != '') {
            $userUuids = explode(',', $request->public_user_uuid);
            $query->whereIn('user_id', $userUuids);
        }

        // Filter by "Submitted By" username (for internal)
        if ($type === 'internal' && $request->has('username') && $request->username != '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->username . '%');
            });
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'inspection_certificates_' . date('d_m_Y_H_i_s') . '.csv';
        $query = $this->getFilteredInspectionQuery($request);
        $applications = $query->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Application ID', 'Date', 'Importer', 'Exporter', 'Status');

        $callback = function () use ($applications, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($applications as $app) {
                fputcsv($file, array(
                    $app->application_id,
                    $app->created_at->format('d-m-Y H:i'),
                    $app->importer->fullname ?? '-',
                    $app->exporter->name ?? '-',
                    strtoupper($app->status)
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredInspectionQuery($request);
        $applications = $query->get();

        $exporterName = null;
        if ($request->has('exporter_id') && $request->exporter_id != '') {
            $exporterName = \App\Models\Exporter::find($request->exporter_id)?->name;
        }

        $importerName = null;
        if ($request->has('importer_id') && $request->importer_id != '') {
            $importerName = \App\Models\PublicUser::where('uuid', $request->importer_id)->first()?->fullname;
        }

        $publicUserName = null;
        if ($request->has('public_user_uuid') && $request->public_user_uuid != '') {
            $publicUserName = \App\Models\PublicUser::where('uuid', $request->public_user_uuid)->first()?->fullname;
        }

        $html = view('pages.public.pdf.inspection_list_pdf', compact('applications', 'exporterName', 'importerName', 'publicUserName'))->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download('inspection_certificates_' . date('d_m_Y_H_i_s') . '.pdf');
    }

    private function bilingualBadge($color, $en, $bm, $time, $user, $id)
    {
        return '
        <span class="badge bg-' . $color . ' fs-12 p-1 activityLog" data-log="' . $id . '" data-en="' . $en . '" data-bm="' . $bm . '">' . $en . '</span>
        <br class="mt-1">
        <small class="text-muted"><span data-en="at" data-bm="pada">at</span> ' . $time . '</small><br>
        <small class="text-muted"><span data-en="by" data-bm="oleh">by</span> ' . e($user) . '</small>
        ';
    }

    public function showAllInspectionList()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return view('pages.public.inspection_list');
    }

    public function getAllInspectionList()
    {
        $type = authUser()['type'];
        $query = $this->getFilteredInspectionQuery(request());

        // Bilingual mappings for status labels
        $statusTranslations = [
            'pending'                       => ['en' => 'Pending', 'bm' => 'Menunggu'],
            'rejected'                      => ['en' => 'Rejected', 'bm' => 'Ditolak'],
            'not approved'                  => ['en' => 'Not Approved', 'bm' => 'Tidak Diluluskan'],
            'accepted'                      => ['en' => 'Accepted', 'bm' => 'Diterima'],
            'officer verification completed' => ['en' => 'Officer Verification Completed', 'bm' => 'Pengesahan Pegawai Selesai'],
            'clerk verified'                => ['en' => 'Clerk Verified', 'bm' => 'Disahkan Kerani'],
            'awaiting approval'             => ['en' => 'Awaiting approval', 'bm' => 'Menunggu Kelulusan'],
            'draft'                         => ['en' => 'Draft', 'bm' => 'Draf'],
            'clerk review in-progress'      => ['en' => 'Clerk review in-progress', 'bm' => 'Semakan Kerani Dalam Proses'],
            'wait for company approval'     => ['en' => 'Wait for company approval', 'bm' => 'Menunggu Kelulusan Syarikat'],
        ];

        // Bilingual mappings for permit status tooltips
        $permitStatusTranslations = [
            'processing'          => ['en' => 'processing', 'bm' => 'Sedang Diproses'],
            'pending for payment' => ['en' => 'pending for payment', 'bm' => 'Menunggu Bayaran'],
            'rejected'            => ['en' => 'rejected', 'bm' => 'Ditolak'],
            'paid'                => ['en' => 'paid', 'bm' => 'Telah Dibayar'],
        ];

        $datatable = DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('application_id', fn($row) => $row->application_id ?? '-')
            ->addColumn('importer', fn($row) => $row->importer->fullname ?? '-')
            ->addColumn('exporter', fn($row) => $row->exporter->name ?? '-')
            ->addColumn('status', function ($row) use ($statusTranslations) {
                $status = strtolower($row->status ?? 'pending');

                $latestLog = $row->latestLog;
                $id = $row->application_id;
                $latestTime = $latestLog?->updated_at?->format('d M Y, h:i A') ?? '-';
                $causerName = $latestLog?->causer?->fullname ?? '-';

                // Find matching translation key
                $matchedKey = null;
                foreach ($statusTranslations as $key => $trans) {
                    if (str_contains($status, $key)) {
                        $matchedKey = $key;
                        break;
                    }
                }

                if ($matchedKey) {
                    $en = $statusTranslations[$matchedKey]['en'];
                    $bm = $statusTranslations[$matchedKey]['bm'];
                    $color = match ($matchedKey) {
                        'pending'     => 'warning',
                        'rejected', 'not approved' => 'danger',
                        'accepted', 'officer verification completed', 'awaiting approval', 'draft', 'clerk review in-progress', 'wait for company approval' => 'success',
                        'clerk verified' => 'info',
                        default => 'secondary',
                    };
                    return $this->bilingualBadge($color, $en, $bm, $latestTime, $causerName, $id);
                }

                return '<span class="badge bg-secondary fs-12 p-1  activityLog"  data-log = "' . $id . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('inspection_status', function ($row) use ($permitStatusTranslations) {
                // Map statuses to colors
                $statusColors = [
                    'processing' => 'bg-info', // blue
                    'pending for payment' => 'bg-warning', // green
                    'rejected' => 'bg-danger', // red
                    'paid' => 'bg-success', // green
                ];

                // Get all permit statuses for this row, lowercase
                $permit_statuses = $row->inspectionItems->pluck('status')->map(fn($status) => strtolower($status))->toArray();

                // Count how many of each status
                $statusCounts = [
                    'processing' => 0,
                    'rejected' => 0,
                    'pending for payment' => 0,
                    'paid' => 0,
                ];

                foreach ($permit_statuses as $status) {
                    if ($status == 'submitted') {
                        $status = 'processing';
                    }
                    if ($status == 'approved') {
                        $status = 'paid';
                    }

                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }

                // Build HTML boxes with count inside
                $boxesHtml = '';
                foreach ($statusColors as $status => $color) {
                    $count = $statusCounts[$status] ?? 0;
                    $trans = $permitStatusTranslations[$status] ?? ['en' => $status, 'bm' => $status];
                    $en = $trans['en'];
                    $bm = $trans['bm'];

                    $boxesHtml .= '<div class="badge ' . $color . ' text-white text-center" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            data-en="' . $count . '" 
                            data-title-en="' . $en . '" 
                            data-title-bm="' . $bm . '" 
                            title="' . $en . '" 
                            style="height:20px; width:20px; display:inline-flex; align-items:center; justify-content:center; margin-right:5px;">
                            ' . $count . '
                       </div>';
                }

                return $boxesHtml;
            })

            ->addColumn('action', function ($row) {
                $url = route('inspection.view_details', ['id' => $row->application_id]);

                $view =
                    '<a class="btn btn-sm btn-primary viewApplication" href="' .
                    $url .
                    '">
                        <i class="ti ti-eye"></i>
                     </a>';

                $delete = '';

                if (authUser()['type'] === 'internal') {
                    $delete =
                        ' <button class="btn btn-sm btn-danger deleteApplication"
                            data-id="' .
                        $row->application_id .
                        '">
                            <i class="ti ti-trash"></i>
                           </button>';
                }

                $download = '<button class="btn btn-sm btn-secondary downloadApplication ms-1" data-id="' . $row->application_id . '" title="Download Application"> <i class="fa-solid fa-print"></i> </button>';

                return $view . $delete . $download;
            });

        if ($type === 'internal') {
            $datatable->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-');
        }

        return $datatable->rawColumns(['status', 'action', 'inspection_status'])->make(true);
    }

    public function getApplicationDetails($id)
    {
        $user = authUser()['user']; // authenticated user object
        $type = authUser()['type'];

        // Fetch application and eager load relationships
        $application = InspectionApplication::where('application_id', $id)
            ->with(['user', 'importer', 'exporter.countryInfo', 'entryPoint.districtCode', 'inspectionItems.attachments', 'activity_log.causer'])
            ->firstOrFail();

        if ($type === 'internal') {
            return response()->json($application);
        }

        if ($type === 'public') {
            // Check if user is either the submitter or the importer
            if ($application->user_id !== $user->uuid && $application->importer_id !== $user->uuid) {
                return response()->json(
                    [
                        'message' => 'You do not have authority to view this application',
                    ],
                    403,
                );
            }

            return response()->json($application);
        }

        // Default fallback
        return response()->json(
            [
                'message' => 'User type not recognized',
            ],
            400,
        );
    }

    function viewInspection($id = null)
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();

        $application = InspectionApplication::with(['exporter', 'importer', 'entryPoint', 'inspectionItems.attachments', 'activity_log.causer'])
            ->where('application_id', $id)
            ->firstOrFail();


        return view('pages.public.view_inspection', compact('pubmeasure', 'pubpurpose', 'country', 'id', 'application'));
    }

    /**
     * Inspection certificate application for self (individual) users.
     */
    public function getInspectionSelf($id = null)
    {
        $blockView = $this->checkDocumentStatusAndReturnView();
        if ($blockView) {
            return $blockView;
        }

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        
        $inspectionDocuments = DocumentRequirement::forModule('inspection')
            ->orderBy('name')
            ->get();
            
        return view('pages.public.inspection_self', compact('pubmeasure', 'pubpurpose', 'country', 'id', 'inspectionDocuments'));
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
    /**
     * Inspection certificate application for others (company / third‑party).
     */
    public function getInspectionOthers($id = null)
    {
        $blockView = $this->checkDocumentStatusAndReturnView();
        if ($blockView) {
            return $blockView;
        }

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        
        $inspectionDocuments = DocumentRequirement::forModule('inspection')
            ->orderBy('name')
            ->get();
            
        return view('pages.public.inspection_others', compact('pubmeasure', 'pubpurpose', 'country', 'id', 'inspectionDocuments'));
    }

    public function saveApplication(Request $request)
    {
        DB::beginTransaction();
        $movedFiles = [];

        try {
            $applicationUuid = $request->input('applicationId');
            $isDraft = $request->boolean('is_draft');
            $isNewApplication = false;

            // Decode JSON data from frontend
            $exporter = $request->exporterData ? json_decode($request->exporterData, true) : null;
            $importer = $request->importerData ? json_decode($request->importerData, true) : null;
            $permit = $request->permitDetails ? json_decode($request->permitDetails, true) : [];

            // Determine status
            if ($isDraft) {
                $status = 'Draft';
            } else {
                if ($permit['applCate'] == 0) {
                    $status = 'Clerk review in-progress';
                } elseif ($permit['applCate'] == 1) {
                    $status = 'wait for company approval';
                }
            }

            $importer_verify = null;
            if (!$isDraft && isset($permit['applCate'])) {
                $importer_verify = $permit['applCate'] == 0 ? 'Clerk Review In-Progress' : 'wait for company approval';
            }

            // ─── Create / Update Application ─────────────────────────────
            if ($applicationUuid) {
                $application = InspectionApplication::where('application_id', $applicationUuid)->firstOrFail();
                $category = $permit['applCate'] ?? 0;
                $verifyDate = null;

                $application->update([
                    'eta'                  => $permit['eta'] ?? null,
                    'transport_type'       => $permit['tranType'] ?? null,
                    'entry_point'          => $permit['entrypoint'] ?? null,
                    'category_application' => $category,
                    'user_id'              => authUser()['user']['uuid'] ?? null,
                    'exporter_id'          => $exporter['id'] ?? null,
                    'importer_id'          => $importer['uuid'] ?? null,
                    'importer_detail'      => $importer ?? [],
                    'status'               => $status,
                    'importer_verify'      => $importer_verify,
                    'date_importer_verify' => $verifyDate,
                ]);

                activity()
                    ->tap(fn($activity) => $activity->log_name = 'user_activity')
                    ->event($isDraft ? 'update draft inspection' : 'update inspection application')
                    ->causedBy(authUser()['user'])
                    ->performedOn($application)
                    ->withProperties(['application' => $application])
                    ->log(authUser()['user']['fullname'] . ($isDraft ? ' has updated a draft inspection (ID: ' : ' has updated an inspection application (ID: ') . $application->application_id . ')');
            } else {
                $category = $permit['applCate'] ?? 0;
                $importerVerify = 'pending';
                $verifyDate = null;

                if ($status !== 'Draft') {
                    if ($category == 0) {
                        $importerVerify = 'Verified';
                        $verifyDate = now();
                    } else {
                        $importerVerify = 'Awaiting approval';
                    }
                }

                $isNewApplication = true;
                $application = InspectionApplication::create([
                    'application_id'       => 'SP' . now()->format('ymd') . random_int(1000, 9999),
                    'eta'                  => $permit['eta'] ?? null,
                    'transport_type'       => $permit['tranType'] ?? null,
                    'entry_point'          => $permit['entrypoint'] ?? null,
                    'category_application' => $category,
                    'user_id'              => authUser()['user']['uuid'] ?? null,
                    'exporter_id'          => $exporter['id'] ?? null,
                    'importer_id'          => $importer['uuid'] ?? null,
                    'importer_detail'      => $importer ?? [],
                    'status'               => $status,
                    'importer_verify'      => $importerVerify,
                    'date_importer_verify' => $verifyDate,
                ]);

                $notificationController = new NotificationController();
                $notificationController->sendStatusMessage(
                    $application->importer_detail['fullname'] ?? 'User',
                    'Inspection Application',
                    $application->application_id,
                    'submitted',
                    'Your application has been successfully submitted.',
                    $application->importer->phone_number ?? '60143290092',
                );

                activity()
                    ->tap(fn($activity) => $activity->log_name = 'user_activity')
                    ->event($isDraft ? 'create draft inspection' : 'create inspection application')
                    ->causedBy(authUser()['user'])
                    ->performedOn($application)
                    ->withProperties(['application' => $application])
                    ->log(authUser()['user']['fullname'] . ($isDraft ? ' has created a new draft inspection (ID: ' : ' has created a new inspection application (ID: ') . $application->application_id . ')');
            }

            $appId = $application->id;

            // ─── Handle old items & their attachments (on update) ──────────
            if ($applicationUuid) {
                // Get existing items with their attachments
                $oldItems = InspectionItem::with('attachments')->where('application_id', $appId)->get();

                foreach ($oldItems as $oldItem) {
                    foreach ($oldItem->attachments as $attachment) {
                        // Delete physical file
                        if ($attachment->file_path) {
                            $path = str_replace('/storage/', '', $attachment->file_path);
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }
                        $attachment->delete();
                    }
                    $oldItem->delete();
                }
            }

            // ─── Save new items ────────────────────────────────────────────
            $itemArray = [];
            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $itemData = json_decode($item['data'], true);

                    $inspectionItem = InspectionItem::create([
                        'application_id'     => $appId,
                        'consignment_detail' => $itemData,
                        'quantity'           => $itemData['quantity'] ?? 0,
                        'unit_measurement'   => $itemData['measure'] ?? null,
                        'value'              => $itemData['value'] ?? 0,
                        'purpose'            => $itemData['purpose'] ?? null,
                        'status'             => 'processing',
                    ]);
                    $itemArray[$index] = $inspectionItem->id;
                }
            }

            // ─── Item Attachments (files[]) ──────────────────────────────
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $i => $file) {
                    $itemIndex = $request->input('file_item_index')[$i] ?? null;
                    if (!isset($itemArray[$itemIndex])) continue;

                    $name = uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('inspection', $name, 'public');
                    $movedFiles[] = $path;

                    InspectionAttachment::create([
                        'item_id'   => $itemArray[$itemIndex],
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => "/storage/{$path}",
                        'file_type' => $file->getClientOriginalExtension(),
                    ]);
                }
            }

            // ─── APPLICATION ATTACHMENTS (application_files[]) ──────────────
            // Using the InspectionApplicationAttachment model
            if ($request->hasFile('application_files')) {
                $documentTypes = $request->input('application_files_document_type', []);
                $descriptions  = $request->input('application_files_description', []);

                foreach ($request->file('application_files') as $i => $file) {
                    $name = uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('inspection_applications', $name, 'public');
                    $movedFiles[] = $path;

                    InspectionApplicationAttachment::create([
                        'application_id' => $appId,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => "/storage/{$path}",
                        'file_type'      => $file->getClientOriginalExtension(),
                        'description'    => $descriptions[$i] ?? ($documentTypes[$i] ?? null),
                    ]);
                }
            }

            // ─── Logging & Notifications ──────────────────────────────────
            if ($isDraft) {
                $application->logActivity(
                    action: $isNewApplication ? 'Draft Created' : 'Draft Updated',
                    remark: $isNewApplication ? 'Inspection application saved as draft' : 'Inspection application draft updated',
                    status: 'Draft'
                );
            } else {
                $application->logActivity(
                    action: 'Submitted',
                    remark: 'Inspection application submitted',
                    status: 'Clerk review in-progress'
                );
                $notificationController = new NotificationController();
                $notificationController->sendStatusMessage(
                    $application->importer_detail['fullname'] ?? 'User',
                    'Inspection Application',
                    $application->application_id,
                    'submitted',
                    'Your application has been successfully submitted.',
                    $application->importer->phone_number ?? '60143290092',
                );
            }

            // Global activity log for inspection_activity
            $actionText = $isDraft
                ? ($isNewApplication ? 'created a draft inspection application' : 'updated draft inspection application')
                : ($isNewApplication ? 'submitted a new inspection application' : 'updated inspection application');

            activity()
                ->tap(fn($activity) => $activity->log_name = 'inspection_activity')
                ->event($isDraft ? ($isNewApplication ? 'draft_created' : 'draft_updated') : ($isNewApplication ? 'application_submitted' : 'application_updated'))
                ->causedBy(authUser()['user'])
                ->performedOn($application)
                ->withProperties([
                    'application_id' => $application->application_id,
                    'status'         => $application->status,
                    'user'           => [
                        'name'  => authUser()['user']->fullname ?? 'Unknown',
                        'email' => authUser()['user']->email ?? 'N/A',
                    ],
                    'importer'       => $application->importer_detail ?? [],
                    'exporter_id'    => $application->exporter_id,
                ])
                ->log(authUser()['user']->fullname . ' ' . $actionText);

            DB::commit();

            // ─── Notifications ──────────────────────────────────────────────
            $notificationUrl = route('public.viewInspectionApplication', ['id' => $application->application_id]);

            $internalUsers = InternalUser::permission('approve application')->get();
            $internalMsg = $isDraft
                ? ($isNewApplication ? 'New Inspection Certificate draft created' : 'Inspection Certificate draft updated')
                : ($isNewApplication ? 'New Inspection Certificate application submitted' : 'Inspection Certificate application updated');
            $internalMsgBm = $isDraft
                ? ($isNewApplication ? 'Draf Sijil Pemeriksaan baru dibuat' : 'Draf Sijil Pemeriksaan dikemaskini')
                : ($isNewApplication ? 'Permohonan Sijil Pemeriksaan baru dihantar' : 'Permohonan Sijil Pemeriksaan dikemaskini');

            if (!$isDraft) {
                Notification::send($internalUsers, new \App\Notifications\ApplicationSubmittedNotification(
                    $internalMsg,
                    $internalMsgBm,
                    auth()->guard('public')->user()?->fullname ?? auth()->user()?->fullname ?? 'System',
                    $notificationUrl,
                    $application->application_id
                ));
            } else {
                Notification::send($internalUsers, new ApplicationNotification(
                    $internalMsg,
                    $internalMsgBm,
                    auth()->guard('public')->user()?->fullname ?? auth()->user()?->fullname ?? 'System',
                    $notificationUrl
                ));
            }

            try {
                event(new InternalUserAdminEvent($internalMsg . ' by ' . (auth()->guard('public')->user()?->fullname ?? auth()->user()?->fullname ?? 'Unknown User')));
                event(new InternalUserClerkEvent($internalMsg . ' by ' . (auth()->guard('public')->user()?->fullname ?? auth()->user()?->fullname ?? 'Unknown User')));
            } catch (\Exception $e) {
                Log::warning('Pusher connection failed but continuing internal notification: ' . $e->getMessage());
            }

            $applicant = auth()->guard('public')->user();
            if ($applicant) {
                $applicantMsg = $isDraft
                    ? 'Your Inspection Certificate Application with id ' . $application->application_id . ' is saved as draft'
                    : 'Your Inspection Certificate Application with id ' . $application->application_id . ' is submitted';

                $applicant->notify(new ApplicationNotification($applicantMsg, $applicantMsg, 'QIS', $notificationUrl));

                try {
                    event(new PublicUserEvent($applicantMsg, $applicant->uuid));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing public notification: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => $isDraft ? 'Draft saved successfully' : 'Application submitted successfully',
                'application_id' => $application->application_id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($movedFiles as $file) {
                Storage::disk('public')->delete($file);
            }
            \Log::error('Error saving inspection application: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save application: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify inspection application permit (Clerk Review In-Progress / Clerk Verified / Officer Verification Completed) for internal users.
     */
    public function updateStatus($id, Request $request)
    {
        $application = InspectionApplication::where('application_id', $id)->firstOrFail();

        // dd($request->all());
        // Centralized messages per status
        $statusMessages = [
            'Clerk review in-progress' => [
                'public' => 'Your inspection application has been received and is now under clerk review.',
                'internal' => 'Inspection application is now under clerk review.',
                'notify' => 'Inspection application is now under clerk review.',
            ],
            'Clerk Verified' => [
                'public' => 'Your inspection application has been approved by the clerk.',
                'internal' => 'Inspection application approved by clerk.',
                'notify' => 'Inspection application has been approved by clerk.',
            ],
            'Officer Verification Completed' => [
                'public' => 'Your inspection application has been officer verification completed.',
                'internal' => 'Inspection application officer verification completed.',
                'notify' => 'Inspection application has been officer verification completed.',
            ],
            'Rejected' => [
                'public' => 'Your inspection application has been rejected.',
                'internal' => 'Inspection application rejected.',
                'notify' => 'Inspection application has been rejected.',
            ],
            'Clerk Rejected' => [
                'public' => 'Your inspection application has been rejected by the clerk.',
                'internal' => 'Inspection application rejected by clerk.',
                'notify' => 'Inspection application has been rejected by clerk.',
            ],
        ];

        $status = $request->input('status');

        if (!isset($statusMessages[$status])) {
            return response()->json(
                [
                    'message' => 'Invalid inspection application status.',
                ],
                400,
            );
        }

        $application->status = $status;
        $notificationController = new NotificationController();

        // Handle verification fields if applicable
        if ($status === 'Clerk review in-progress') {
            $application->importer_verify = 'Verified';
        } elseif ($status === 'Rejected' || $status === 'Clerk Rejected') {
            $application->importer_verify = 'Rejected';

            $notificationController->sendStatusMessage(
                $application->importer_detail['fullname'] ?? 'User',
                'Inspection Application',
                $application->application_id,
                'has been rejected by DOA',
                'Your application is rejected.',
                $application->importer->phone_number ?? '60143290092', // recipient number
            );
        } elseif ($status === 'Clerk Verified') {
            $application->importer_verify = 'Accepted';

            $notificationController->sendStatusMessage(
                $application->importer_detail['fullname'] ?? 'User',
                'Inspection Application',
                $application->application_id,
                'has been accepted by DOA',
                'Your application is under review and will be processed shortly.',
                $application->importer->phone_number ?? '60143290092', // recipient number
            );
        }

        $application->save();

        // activity log
        $application->logActivity(action: $status, remark: $request->input('reason') ?? "Inspection application {$status}", status: $status);

        // Global activity log for inspection_activity
        $logMessage = match ($status) {
            'Clerk Verified' => authUser()['user']->fullname . ' verified inspection certificate application ' . $application->application_id,
            'Rejected', 'Clerk Rejected' => authUser()['user']->fullname . ' rejected inspection certificate application ' . $application->application_id,
            'Clerk review in-progress' => authUser()['user']->fullname . ' moved inspection certificate application ' . $application->application_id . ' to clerk review',
            default => authUser()['user']->fullname . ' updated inspection certificate application ' . $application->application_id . ' status to ' . $status,
        };

        activity()
            ->tap(function ($activity) {
                $activity->log_name = 'inspection_activity';
            })
            ->event('status_updated')
            ->causedBy(authUser()['user'])
            ->performedOn($application)
            ->withProperties([
                'application_id' => $application->application_id,
                'old_status' => $application->getOriginal('status'),
                'new_status' => $status,
                'reason' => $request->input('reason'),
                'user' => [
                    'name' => authUser()['user']->fullname ?? 'Unknown',
                    'email' => authUser()['user']->email ?? 'N/A',
                ],
            ])
            ->log($logMessage);

        $messages = $statusMessages[$status];

        $notificationUrl = route('public.viewInspectionApplication', ['id' => $application->application_id]);

        /**
         * =====================
         * INTERNAL USER EVENT + NOTIFICATION
         * =====================
         */
        try {
            event(new InternalUserAdminEvent($messages['internal']));
            event(new InternalUserClerkEvent($messages['internal']));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed but continuing internal notification: ' . $e->getMessage());
        }

        $internalUsers = InternalUser::role(['admin', 'clerk', 'superadmin'])->get();
        Notification::send($internalUsers, new ApplicationNotification($messages['notify'], $messages['notify'], authUser()['user']->fullname, $notificationUrl));

        /**
         * =====================
         * PUBLIC USER (APPLICANT)
         * =====================
         */
        $publicUser = PublicUser::where('uuid', $application->user_id)->first();

        try {
            event(new PublicUserEvent($messages['public'], $publicUser->uuid));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed but continuing public notification: ' . $e->getMessage());
        }

        Notification::send($publicUser, new ApplicationNotification($messages['public'], $messages['public'], authUser()['user']->fullname, $notificationUrl));


        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->event(strtolower($status) . ' inspection application')
            ->causedBy(authUser()['user'])
            ->performedOn($application)
            ->withProperties([
                'status' => $status,
            ])
            ->log(authUser()['user']['fullname'] . ' has ' . strtolower($status) . ' an inspection application (ID: ' . $application->application_id . ')');

        return response()->json([
            'message' => 'Inspection application status updated successfully.',
            'status' => $status,
        ]);
    }

    /**
     * Delete an inspection application and its related data (internal only).
     */
    public function deleteApplication($id)
    {
        $userData = authUser();
        $user = $userData['user'];
        $type = $userData['type'];

        return DB::transaction(function () use ($id, $user, $type) {
            $application = InspectionApplication::where('application_id', $id)->firstOrFail();
            $applicationId = $application->application_id;
            $applicantUuid = $application->user_id;

            // Authorization: public users can only delete their own
            if ($type === 'public' && $application->user_id !== $user->uuid && $application->importer_id !== $user->uuid) {
                return response()->json(
                    [
                        'message' => 'Unauthorized to delete this application.',
                    ],
                    403,
                );
            }

            $items = \App\Models\InspectionItem::where('application_id', $application->id)->get();

            if ($items->isNotEmpty()) {
                $itemIds = $items->pluck('id');

                $attachments = \App\Models\InspectionAttachment::whereIn('item_id', $itemIds)->get();

                foreach ($attachments as $attachment) {
                    if ($attachment->file_path) {
                        $path = str_replace('/storage/', '', $attachment->file_path);

                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }

                    $attachment->delete();
                }

                \App\Models\InspectionItem::whereIn('id', $itemIds)->delete();
            }

            $application->delete();

            activity()
                ->tap(function (Activity $activity) {
                    $activity->log_name = 'user_activity';
                })
                ->event('delete inspection application')
                ->causedBy(authUser()['user'])
                ->performedOn(authUser()['user'])
                ->withProperties([
                    'application_id' => $application->application_id,
                ])
                ->log(authUser()['user']['fullname'] . ' has deleted an inspection application (ID: ' . $application->application_id . ')');
            // activity log and notifications
            $application->logActivity(action: 'Deleted', remark: 'Inspection application deleted', status: 'Deleted');

            // Global activity log for inspection_activity
            activity()
                ->tap(function ($activity) {
                    $activity->log_name = 'inspection_activity';
                })
                ->event('application_deleted')
                ->causedBy(authUser()['user'])
                ->performedOn($application)
                ->withProperties([
                    'application_id' => $applicationId,
                    'applicant_uuid' => $applicantUuid,
                    'deleted_by' => [
                        'name' => authUser()['user']->fullname ?? 'Unknown',
                        'email' => authUser()['user']->email ?? 'N/A',
                        'type' => $type,
                    ],
                ])
                ->log(authUser()['user']->fullname . ' deleted inspection application ' . $applicationId);

            $notificationUrl = route('public.showallinspectionlist');
            $internalUsers = InternalUser::role(['admin', 'clerk', 'superadmin'])->get();
            Notification::send($internalUsers, new ApplicationNotification("Inspection application {$applicationId} has been deleted", "Inspection application {$applicationId} has been deleted", authUser()['user']->fullname, $notificationUrl));

            $applicant = PublicUser::where('uuid', $applicantUuid)->first();
            if ($applicant) {
                $applicantMsg = "Your inspection application with id {$applicationId} has been deleted";
                $applicant->notify(new ApplicationNotification($applicantMsg, $applicantMsg, 'QIS', $notificationUrl));

                try {
                    event(new PublicUserEvent($applicantMsg, $applicant->uuid));
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing public notification: ' . $e->getMessage());
                }
            }

            return response()->json([
                'message' => 'Inspection application and all attachments deleted successfully.',
            ]);
        });
    }

    public function viewApplication($id)
    {
        $application = InspectionApplication::with(['exporter', 'importer', 'entryPoint', 'inspectionItems.attachments'])
            ->where('application_id', $id)
            ->firstOrFail();

        return view('pages.public.view_inspection', compact('application'));
    }

    public function getApplicationData($id)
    {
        $application = InspectionApplication::with(['exporter', 'importer', 'entryPoint', 'inspectionItems.attachments', 'activity_log.causer'])
            ->where('application_id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $application,
            'activity_log' => $application->activity_log,
        ]);
    }

    public function acceptInspectionItem($id, Request $request)
    {
        // Check if user has proper role (Officer or Admin)
        $user = authUser()['user'];
        if (!$user->hasAnyRole(['officer', 'admin', 'superadmin'])) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Unauthorized: Only Officers and Administrators can approve inspection items.',
                ],
                403,
            );
        }

        // $inspectionItem = \App\Models\InspectionItem::findOrFail($id);
        $inspectionApplication = InspectionApplication::where('application_id', $id)->first();

        // Check if application is in correct status for item-level actions
        // $application = $inspectionItem->application;
        if (!str_contains(strtolower($inspectionApplication->status), 'clerk verified')) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Items can only be approved after Clerk verification.',
                ],
                403,
            );
        }

        // Update item status
        // $inspectionItem->permit_number = 'SP/' . now()->format('ymd') . rand(1000, 9999);
        // $inspectionItem->status = 'pending for payment';
        // $inspectionItem->save();

        // $detail = $inspectionItem->consignment_detail;
        // $itemName = $detail['item_name'] ?? 'Item';

        // find all items
        $items = $inspectionApplication->inspectionItems;
        $permit_number =  'SP/' . now()->format('ymd') . rand(1000, 9999);
        foreach ($items as $item) {
            $item->permit_number = $permit_number;
            $item->status = 'pending for payment';
            $item->save();
        }

        // Log the activity
        $inspectionApplication->logActivity(action: 'Item Accepted', remark: 'All Inspection Item are accepted', status: 'Item Accepted');

        // Global activity log
        activity()
            ->tap(function ($activity) {
                $activity->log_name = 'inspection_activity';
            })
            ->event('item_accepted')
            ->causedBy($user)
            ->performedOn($inspectionApplication)
            ->withProperties([
                'application_id' => $inspectionApplication->application_id,

                'status' => 'Item Accepted',
                'user' => [
                    'name' => $user->fullname ?? 'Unknown',
                    'email' => $user->email ?? 'N/A',
                ],
            ])
            ->log($user->fullname . " accepted inspection item '");

        // Notifications
        $notificationUrl = route('public.viewInspectionApplication', ['id' => $inspectionApplication->application_id]);
        $msg = 'All Inspection item has been accepted';

        // Internal Notification
        try {
            event(new InternalUserAdminEvent($msg . ' by ' . $user->fullname));
            event(new InternalUserClerkEvent($msg . ' by ' . $user->fullname));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed: ' . $e->getMessage());
        }

        $internalUsers = InternalUser::role(['admin', 'clerk', 'superadmin'])->get();
        Notification::send($internalUsers, new ApplicationNotification($msg, $msg, $user->fullname, $notificationUrl));

        // Public Notification
        $publicUser = PublicUser::where('uuid', $inspectionApplication->user_id)->first();
        if ($publicUser) {
            try {
                event(new PublicUserEvent($msg, $publicUser->uuid));
            } catch (\Exception $e) {
                Log::warning('Pusher connection failed: ' . $e->getMessage());
            }
            Notification::send($publicUser, new ApplicationNotification($msg, $msg, $user->fullname, $notificationUrl));
        }

        // Check if all items are processed (either approved or rejected)
        $allItemsProcessed = $inspectionApplication->inspectionItems->every(function ($item) {
            return in_array($item->status, ['pending for payment', 'rejected']);
        });

        if ($allItemsProcessed) {
            $inspectionApplication->status = 'Officer Verification Completed';
            $inspectionApplication->save();

            $inspectionApplication->logActivity(action: 'Officer Verification Completed', remark: 'All inspection items processed', status: 'Officer Verification Completed');

            $notificationController = new NotificationController();

            $notificationController->sendStatusMessage(
                $inspectionApplication->importer_detail['fullname'] ?? 'User',
                'Inspection Application',
                $inspectionApplication->application_id,
                'checked by DOA',
                "All your application's inspection items have been checked by DOA. Please reapply any rejected items.",
                $inspectionApplication->importer->phone_number ?? '+60143290092', // recipient number
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Inspection item approved successfully.',
        ]);
    }

    public function rejectInspectionItem($id, Request $request)
    {
        // Check if user has proper role (Officer or Admin)

        $user = authUser()['user'];
        if (!$user->hasAnyRole(['officer', 'admin', 'superadmin'])) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Unauthorized: Only Officers and Administrators can reject inspection items.',
                ],
                403,
            );
        }

        $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $application = InspectionApplication::where('application_id', $id)->first();

        // Check if application is in correct status for item-level actions
        // $application = $inspectionItem->application;
        if (!str_contains(strtolower($application->status), 'clerk verified')) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Items can only be rejected after Clerk verification.',
                ],
                403,
            );
        }

        $inspectionItems = $application->inspectionItems;

        foreach ($inspectionItems as $inspectionItem) {
            // Update item status
            $inspectionItem->status = 'rejected';
            $inspectionItem->remark = $request->reason;
            $inspectionItem->save();
        }

        $detail = $inspectionItem->consignment_detail;
        $itemName = $detail['item_name'] ?? 'Item';

        // Log the activity
        $application->logActivity(action: 'Item Rejected', remark: "Inspection item '{$itemName}' rejected. Reason: " . $request->reason, status: 'Item Rejected');

        // Global activity log
        activity()
            ->tap(function ($activity) {
                $activity->log_name = 'inspection_activity';
            })
            ->event('item_rejected')
            ->causedBy($user)
            ->performedOn($application)
            ->withProperties([
                'application_id' => $application->application_id,
                'item_id' => $id,
                'item_name' => $itemName,
                'reason' => $request->reason,
                'status' => 'Item Rejected',
                'user' => [
                    'name' => $user->fullname ?? 'Unknown',
                    'email' => $user->email ?? 'N/A',
                ],
            ])
            ->log($user->fullname . " rejected inspection item '{$itemName}'");

        // Notifications
        $notificationUrl = route('public.viewInspectionApplication', ['id' => $application->application_id]);
        $msg = "Inspection item '{$itemName}' has been rejected";

        // Internal Notification
        try {
            event(new InternalUserAdminEvent($msg . ' by ' . $user->fullname));
            event(new InternalUserClerkEvent($msg . ' by ' . $user->fullname));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed: ' . $e->getMessage());
        }

        $internalUsers = InternalUser::role(['admin', 'clerk', 'superadmin'])->get();
        Notification::send($internalUsers, new ApplicationNotification($msg, $msg, $user->fullname, $notificationUrl));

        // Public Notification
        $publicUser = PublicUser::where('uuid', $application->user_id)->first();
        if ($publicUser) {
            try {
                event(new PublicUserEvent($msg, $publicUser->uuid));
            } catch (\Exception $e) {
                Log::warning('Pusher connection failed: ' . $e->getMessage());
            }
            Notification::send($publicUser, new ApplicationNotification($msg, $msg, $user->fullname, $notificationUrl));
        }

        // Check if all items are processed (either approved or rejected)
        $allItemsProcessed = $application->inspectionItems->every(function ($item) {
            return in_array($item->status, ['pending for payment', 'rejected']);
        });

        if ($allItemsProcessed) {
            $application->status = 'Officer Verification Completed';
            $application->save();

            $application->logActivity(action: 'Officer Verification Completed', remark: 'All inspection items processed', status: 'Officer Verification Completed');


            $notificationController = new NotificationController();

            $notificationController->sendStatusMessage(
                $application->importer_detail['fullname'] ?? 'User',
                'Inspection Application',
                $application->application_id,
                'checked by DOA',
                "All your application's inspection items have been checked by DOA. Please reapply any rejected items.",
                $application->importer->phone_number ?? '+60143290092', // recipient number
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Inspection item rejected successfully.',
        ]);
    }

    public function reapply($id, Request $request)
    {
        $permit = InspectionItem::with(['application', 'attachments'])->findOrFail($id);

        foreach ($permit->attachments as $attachment) {
            // Remove file from storage
            if ($attachment->file_path) {
                // file_path = "/storage/import/xxx.jpg"
                $storagePath = str_replace('/storage/', '', $attachment->file_path);

                Storage::disk('public')->delete($storagePath);
            }

            // Remove DB record
            $attachment->delete();
        }

        // 1️⃣ Get item data
        $item = $request->items[0] ?? null;
        if (!$item || !isset($item['data'])) {
            return response()->json(['message' => 'Invalid item data'], 422);
        }

        $data = json_decode($item['data'], true);

        // dd($data);

        // 2️⃣ Update permit fields
        $permit->update([
            'consignment_detail' => $data,
            'quantity' => $data['quantity'] ?? $permit->quantity,
            'unit_measurement' => $data['measure'] ?? $permit->unit_measurement,
            'value' => $data['value'] ?? $permit->value,
            'purpose' => $data['purpose'] ?? $permit->purpose,
            'status' => 'reapplied',
        ]);

        // 3️⃣ Save attachments (single permit)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $name = uniqid() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('import', $name, 'public');

                InspectionAttachment::create([
                    'item_id' => $permit->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => "/storage/{$path}",
                    'file_type' => $file->getClientOriginalExtension(),
                ]);
            }
        }

        $application = $permit->application;

        $application->logActivity(action: 'Consignment Reapply', remark: 'User reapply the consignment', status: 'User Reapply Consignment');

        $application->status = 'Clerk Verified';
        $application->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Permit updated and files uploaded successfully',
        ]);
    }


    public function printInspection($id)
    {
        $application = InspectionApplication::with([
            'user',
            'exporter',
            'importer',
            'entryPoint',
            'inspectionItems',
        ])->where('application_id', $id)->firstOrFail();

        $pdf = Pdf::loadView('pdf.inspection_application', compact('application'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Import_Permit_Application_{$application->application_id}.pdf");
    }
}
