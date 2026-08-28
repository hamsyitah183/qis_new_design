<?php

namespace App\Http\Controllers;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\ConsignmentApplication;
use App\Models\ConsignmentImporter;
use App\Models\Country;
use App\Models\Exporter;
use App\Models\InspectionApplication;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpConsignmentAttachment;
use App\Models\InspectionItem;
use App\Models\IpConsignmentPermit;
use App\Models\ConsignmentPermit;
use App\Models\DocumentRequirement;
use App\Models\Order;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Models\QrScanLog;
use App\Models\UserAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{
    private function getFilteredApplicationQuery(Request $request)
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = IpApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog.causer']);

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

        // Filter by importer ID (importer is a PublicUser UUID)
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
        $fileName = 'import_permit_applications_' . date('d_m_Y_H_i_s') . '.csv';
        $query = $this->getFilteredApplicationQuery($request);
        $applications = $query->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('App ID', 'Date', 'Importer', 'Exporter', 'Status');

        $callback = function () use ($applications, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($applications as $app) {
                $row['App ID'] = $app->application_id;
                $row['Date'] = $app->created_at->format('d-m-Y H:i');
                $row['Importer'] = $app->importer->fullname ?? '-';
                $row['Exporter'] = $app->exporter->name ?? '-';
                $row['Status'] = strtoupper($app->status);

                fputcsv($file, array($row['App ID'], $row['Date'], $row['Importer'], $row['Exporter'], $row['Status']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredApplicationQuery($request);
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

        $html = view('pages.public.pdf.application_list_pdf', compact('applications', 'exporterName', 'importerName', 'publicUserName'))->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download('import_permit_applications_' . date('d_m_Y_H_i_s') . '.pdf');
    }

    //
    public function show()
    {
        $user = auth()->user();
        if ($user->doa_verified) {
            return view('pages.public.apply_new');
        }

        // ─── Not verified – check required documents ──────────────────
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

        return view('pages.public.wait_for_verified', compact('docStatus'));
    }

    public function showthis()
    {
        return view('pages.public.formw');
    }

    public function showallapplicationlist()
    {
        return view('pages.public.application_list');
    }


    public function getallapplicationlist()
    {
        $type = authUser()['type'];
        $query = $this->getFilteredApplicationQuery(request());

        // Bilingual mappings for status labels
        $statusTranslations = [
            'pending'                       => ['en' => 'Pending', 'bm' => 'Menunggu'],
            'rejected'                      => ['en' => 'Rejected', 'bm' => 'Ditolak'],
            'not approved'                  => ['en' => 'Not Approved', 'bm' => 'Tidak Diluluskan'],
            'accepted'                      => ['en' => 'Accepted', 'bm' => 'Diterima'],
            'officer verification completed' => ['en' => 'Officer Verification Completed', 'bm' => 'Pengesahan Pegawai Selesai'],
            'clerk verified'                => ['en' => 'Clerk Verified', 'bm' => 'Disahkan Kerani'],

            // Add any other statuses you have
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
                        'accepted', 'officer verification completed' => 'success',
                        'clerk verified' => 'info',
                        default => 'secondary',
                    };
                    return $this->bilingualBadge($color, $en, $bm, $latestTime, $causerName, $id);
                }

                // Fallback for unknown status
                return '<span class="badge bg-secondary fs-12 p-1 activityLog" data-log="' . $id . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('permit_status', function ($row) use ($permitStatusTranslations) {
                $statusColors = [
                    'processing' => 'bg-info',
                    'pending for payment' => 'bg-warning',
                    'rejected' => 'bg-danger',
                    'paid' => 'bg-success',
                ];

                $permit_statuses = $row->consignmentPermits->pluck('status')->map(fn($s) => strtolower($s))->toArray();

                $statusCounts = [
                    'processing' => 0,
                    'rejected' => 0,
                    'pending for payment' => 0,
                    'paid' => 0,
                ];

                foreach ($permit_statuses as $status) {
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }

                $boxesHtml = '';
                foreach ($statusColors as $status => $color) {
                    $count = $statusCounts[$status] ?? 0;
                    $trans = $permitStatusTranslations[$status] ?? ['en' => $status, 'bm' => $status];
                    $en = $trans['en'];
                    $bm = $trans['bm'];

                    // Tooltip (title) is bilingual via data-en/data-bm;
                    // the visible text is just the numeric count.
                    $boxesHtml .= '<div class="badge ' . $color . ' text-white text-center" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            title="' . $status . '" 
                        
                            style="height:20px; width:20px; display:inline-flex; align-items:center; justify-content:center; margin-right:5px;">
                            ' . $count . '
                       </div>';
                }
                return $boxesHtml;
            })
            ->addColumn('action', function ($row) {
                $url = '/view_application/' . $row->application_id;
                $view = '<a class="btn btn-sm btn-primary viewApplication" href="' . $url . '"><i class="ti ti-eye"></i></a>';
                $delete = '';
                if (authUser()['type'] === 'internal') {
                    $delete = '<button class="btn btn-sm btn-danger deleteApplication" data-id="' . $row->application_id . '"><i class="ti ti-trash"></i></button>';
                }
                return $view . ' ' . $delete;
            });

        if ($type === 'internal') {
            $datatable->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-');
        }

        return $datatable->rawColumns(['status', 'action', 'permit_status'])->make(true);
    }

    /**
     * Generate a bilingual status badge with data-en and data-bm.
     */
    private function bilingualBadge($color, $en, $bm, $time, $user, $id)
    {
        return '
        <span class="badge bg-' . $color . ' fs-12 p-1 activityLog" data-log="' . $id . '" data-en="' . $en . '" data-bm="' . $bm . '">' . $en . '</span>
        <br class="mt-1">
        <small class="text-muted">at ' . $time . '</small><br>
        <small class="text-muted">by ' . e($user) . '</small>
    ';
    }

    public function getAllReviewapplicationList()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        // Import Permit
        $ip = IpApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode'])
            ->whereIn('status', [
                'awaiting approval',
                'wait for company approval',
            ])
            ->when($type === 'public', function ($q) use ($userUuid) {
                $q->where('category_application', 1)
                    ->where('importer_id', $userUuid);
            })
            ->get()
            ->map(function ($item) {
                $item->application_source = 'import_permit';
                $item->url = '/view_application/' . $item->application_id;
                return $item;
            });


        // Consignment
        $consignment = ConsignmentApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode'])
            ->whereIn('status', [
                'awaiting approval',
                'wait for company approval',
            ])
            ->when($type === 'public', function ($q) use ($userUuid) {
                $q->where('category_application', 1)
                    ->where('exporter_id', $userUuid);
            })
            ->get()
            ->map(function ($item) {
                $item->application_source = 'consignment';
                $item->url = '/view_consignment/' . $item->application_id;
                return $item;
            });


        // Inspection
        $inspection = InspectionApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode'])
            ->whereIn('status', [
                'awaiting approval',
                'wait for company approval',
            ])
            ->when($type === 'public', function ($q) use ($userUuid) {
                $q->where('category_application', 1)
                    ->where('importer_id', $userUuid);
            })
            ->get()
            ->map(function ($item) {
                $item->application_source = 'inspection';
                $item->url = '/view_inspection_certificates/' . $item->application_id;
                return $item;
            });


        // ✅ Merge everything
        $applications = collect()->merge($ip)->merge($consignment)->merge($inspection)->sortByDesc('created_at')->values();

        return DataTables::of($applications)
            ->addIndexColumn()

            ->addColumn('application_type', function ($row) {
                return match ($row->application_source) {
                    'import_permit' => '<span class="badge bg-info">Import Permit</span>',
                    'consignment' => '<span class="badge bg-warning">Consignment</span>',
                    'inspection' => '<span class="badge bg-success">Inspection</span>',
                };
            })

            ->addColumn('importer', fn($row) => $row->importer?->fullname ?? ($row->importer?->name ?? '-'))

            ->addColumn('exporter', fn($row) => $row->exporter?->name ?? ($row->exporter?->fullname ?? '-'))
            ->addColumn('submitted_by', fn($row) => $row->user?->fullname ?? '-')

            // ->addColumn('importer_type', function ($row) {
            //     $type = $row->category_application == 1 ? 'Others' : 'Self';
            //     return '<span class="badge bg-primary-transparent fs-13 p-1">' . $type . '</span>';
            // })

            // ->addColumn('date', fn($row) => $row->eta ? $row->eta->format('Y-m-d') : '-')

            ->addColumn('status', function ($row) {
                $status = strtolower($row->status ?? 'pending');

                return match (true) {
                    str_contains($status, 'pending') => '<span class="badge bg-warning fs-11 p-2">Pending</span>',
                    str_contains($status, 'rejected') => '<span class="badge bg-danger fs-11 p-2">Rejected</span>',
                    str_contains($status, 'approved'), str_contains($status, 'success') => '<span class="badge bg-success fs-11 p-2">Approved</span>',
                    default => '<span class="badge bg-secondary fs-11 p-2">' . ucfirst($status) . '</span>',
                };
            })

            ->addColumn('action', function ($row) {
                return '<a class="btn btn-sm btn-primary viewApplication"
                            href="' .
                    $row->url .
                    '">
                            View
                        </a>';
            })

            ->rawColumns(['status', 'importer_type', 'action', 'application_type'])
            ->make(true);
    }

    public function getAllAgentApplicationList()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $ip = IpApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode'])->when($type === 'public', function ($q) use ($userUuid) {
            $q->where('category_application', 1)->where('importer_id', $userUuid);
        })->get()->map(function ($item) {
            $item->application_source = 'import_permit';
            $item->url = '/view_application/' . $item->application_id;
            return $item;
        });
        // Consignment $consignment = 
        $consignment = ConsignmentApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode'])->when($type === 'public', function ($q) use ($userUuid) {
            $q->where('category_application', 1)->where('exporter_id', $userUuid);
        })->get()->map(function ($item) {
            $item->application_source = 'consignment';
            $item->url = '/view_consignment/' . $item->application_id;
            return $item;
        });
        // Inspection $inspection = 
        $inspection = InspectionApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode'])->when($type === 'public', function ($q) use ($userUuid) {
            $q->where('category_application', 1)->where('importer_id', $userUuid);
        })->get()->map(function ($item) {
            $item->application_source = 'inspection';
            $item->url = '/view_inspection_certificates/' . $item->application_id;
            return $item;
        });


        // ✅ Merge everything
        $applications = collect()->merge($ip)->merge($consignment)->merge($inspection)->sortByDesc('created_at')->values();

        return DataTables::of($applications)
            ->addIndexColumn()

            ->addColumn('application_type', function ($row) {
                return match ($row->application_source) {
                    'import_permit' => '<span class="badge bg-info">Import Permit</span>',
                    'consignment' => '<span class="badge bg-warning">Consignment</span>',
                    'inspection' => '<span class="badge bg-success">Inspection</span>',
                };
            })

            ->addColumn('importer', fn($row) => $row->importer?->fullname ?? ($row->importer?->name ?? '-'))

            ->addColumn('exporter', fn($row) => $row->exporter?->name ?? ($row->exporter?->fullname ?? '-'))
            ->addColumn('submitted_by', fn($row) => $row->user?->fullname ?? '-')

            // ->addColumn('importer_type', function ($row) {
            //     $type = $row->category_application == 1 ? 'Others' : 'Self';
            //     return '<span class="badge bg-primary-transparent fs-13 p-1">' . $type . '</span>';
            // })

            // ->addColumn('date', fn($row) => $row->eta ? $row->eta->format('Y-m-d') : '-')

            ->addColumn('status', function ($row) {
                $status = strtolower($row->status ?? 'pending');

                return match (true) {
                    str_contains($status, 'pending') => '<span class="badge bg-warning fs-11 p-2">Pending</span>',
                    str_contains($status, 'rejected') => '<span class="badge bg-danger fs-11 p-2">Rejected</span>',
                    str_contains($status, 'approved'), str_contains($status, 'success') => '<span class="badge bg-success fs-11 p-2">Approved</span>',
                    default => '<span class="badge bg-secondary fs-11 p-2">' . ucfirst($status) . '</span>',
                };
            })

            ->addColumn('action', function ($row) {
                return '<a class="btn btn-sm btn-primary viewApplication"
                            href="' .
                    $row->url .
                    '">
                            View
                        </a>';
            })

            ->rawColumns(['status', 'importer_type', 'action', 'application_type'])
            ->make(true);
    }

    public function deleteApplication($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $application = IpApplication::where('application_id', $id)->firstOrFail();

                $consignments = IpConsignmentPermit::where('application_id', $application->id)->get();

                if ($consignments->isNotEmpty()) {
                    $consignmentIds = $consignments->pluck('id');

                    // 🔥 Get attachments FIRST
                    $attachments = IpConsignmentAttachment::whereIn('permit_id', $consignmentIds)->get();

                    foreach ($attachments as $attachment) {
                        if ($attachment->file_path) {
                            // Convert "/storage/import/xxx.pdf" → "import/xxx.pdf"
                            $path = str_replace('/storage/', '', $attachment->file_path);

                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }

                        // Delete DB record
                        $attachment->delete();
                    }

                    // Delete consignments
                    IpConsignmentPermit::whereIn('id', $consignmentIds)->delete();
                }
                $user = PublicUser::where('uuid', $application->user_id)->first();
                // Delete application
                $application->delete();

                try {
                    // Events & notifications
                    event(new ApplicationDeleted('Application with ID ' . $id . ' has been deleted.'));

                    if ($user) {
                        event(new PublicUserEvent('Your Application with ID ' . $id . ' has been deleted.', $user->uuid));
                    }
                } catch (\Exception $e) {
                    Log::warning('Pusher connection failed but continuing application deletion: ' . $e->getMessage());
                }

                $authUserName = authUser() ? (authUser()['user']->fullname ?? 'Admin') : 'Admin';
                if ($user) {
                    $msgEn = 'Import Application with ID ' . $id . ' has been deleted.';
                    $msgBm = 'Permohonan Import dengan ID ' . $id . ' telah dipadam.';
                    Notification::send($user, new ApplicationNotification($msgEn, $msgBm, $authUserName));
                }

                return response()->json([
                    'message' => 'Application and all attachments deleted successfully.',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Application Deletion Failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function verifyapplication()
    {
        $userId = auth()->id();

        // Import Permit
        $ipApplications = IpApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog'])
            ->where('importer_id', $userId)
            ->where('category_application', true)
            ->get()
            ->map(function ($item) {
                $item->application_source = 'import_permit';
                return $item;
            });

        // Consignment
        $consignmentApplications = ConsignmentApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog'])
            ->where('importer_id', $userId)
            ->where('category_application', true)
            ->get()
            ->map(function ($item) {
                $item->application_source = 'consignment';
                return $item;
            });

        // Inspection
        $inspectionApplications = InspectionApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog'])
            ->where('importer_id', $userId)
            ->where('category_application', true)
            ->get()
            ->map(function ($item) {
                $item->application_source = 'inspection';
                return $item;
            });

        // ✅ Combine all
        $applications = $ipApplications->merge($consignmentApplications)->merge($inspectionApplications)->sortByDesc('created_at')->values();

        return view('pages.public.application_review_list', compact('applications'));
    }

    public function agentList()
    {
        $userId = auth()->id();

        // Import Permit
        $ipApplications = IpApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog'])
            ->where('importer_id', $userId)
            ->where('category_application', true)
            ->get()
            ->map(function ($item) {
                $item->application_source = 'import_permit';
                return $item;
            });

        // Consignment
        $consignmentApplications = ConsignmentApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog'])
            ->where('importer_id', $userId)
            ->where('category_application', true)
            ->get()
            ->map(function ($item) {
                $item->application_source = 'consignment';
                return $item;
            });

        // Inspection
        $inspectionApplications = InspectionApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'latestLog'])
            ->where('importer_id', $userId)
            ->where('category_application', true)
            ->get()
            ->map(function ($item) {
                $item->application_source = 'inspection';
                return $item;
            });

        // ✅ Combine all
        $applications = $ipApplications->merge($consignmentApplications)->merge($inspectionApplications)->sortByDesc('created_at')->values();

        return view('pages.public.application_agent_list', compact('applications'));
    }

    public function viewapplication($uuid)
    {
        Artisan::call('bayupay:check-pending');

        $application = IpApplication::with([
            'user', // submitted by
            'importer', // importer user
            'exporter', // exporter record
            // 'exporter.country',
            'entryPoint.districtCode',
        ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        $itemId = $application->id;

        // dd($application->consignmentPermits);

        $consignment = IpConsignmentPermit::with(['unit', 'purposeCode'])
            ->where('application_id', $itemId)
            ->get();

        // dd($consignment);

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();

        return view('pages.public.view_application', [
            'application' => $application,
            'consignment' => $consignment,
            'pubmeasure' => $pubmeasure,
            'pubpurpose' => $pubpurpose,
            'country' => $country,
            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }

    public function viewapplicationTest($uuid)
    {
        Artisan::call('bayupay:check-pending');

        $application = IpApplication::with([
            'user', // submitted by
            'importer', // importer user
            'exporter', // exporter record
            // 'exporter.country',
            'entryPoint.districtCode',
        ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        $itemId = $application->id;

        // dd($application->consignmentPermits);

        $consignment = IpConsignmentPermit::with(['unit', 'purposeCode'])
            ->where('application_id', $itemId)
            ->get();

        // dd($consignment);

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();

        return view('pages.public.view_application_test', [
            'application' => $application,
            'consignment' => $consignment,
            'pubmeasure' => $pubmeasure,
            'pubpurpose' => $pubpurpose,
            'country' => $country,
        ]); //, 'consignment', 'attachment'
    }


    public function applyTest()
    {
        return view('pages.public.view_permit_test.applyImport', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function applyTestBahasa()
    {
        return view('pages.public.view_permit_test.applyImportBm', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function summaryTest()
    {
        return view('pages.public.view_permit_test.application_summary', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function listTest()
    {
        return view('pages.public.view_permit_test.application_list_test', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function verifyTest()
    {
        return view('pages.public.view_permit_test.application_clerk', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function approveTest()
    {
        return view('pages.public.view_permit_test.application_officer', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }

    public function paymentTest()
    {
        return view('pages.public.view_permit_test.application_payment', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function orderTest()
    {
        return view('pages.public.view_permit_test.order_payment', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function receiptTest()
    {
        return view('pages.public.view_permit_test.receipt', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function controlPanelTest()
    {
        return view('pages.public.view_permit_test.control_panel_test', [

            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }

    public function editApplication($uuid)
    {
        $application = IpApplication::with([
            'user', // submitted by
            'importer', // importer user
            'exporter', // exporter record
            // 'exporter.country',
            'entryPoint.districtCode',
            'consignmentPermits.attachments',
        ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        if ($application->user_id != authUser()['user']->uuid || $application->status != 'Draft') {
            abort(403, 'Cannot edit this application.');
        }

        $itemId = $application->id;

        // dd($application->consignmentPermits);

        $consignment = IpConsignmentPermit::with(['unit', 'purposeCode'])
            ->where('application_id', $itemId)
            ->get();

        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.edit_permit', compact('pubmeasure', 'pubpurpose', 'country', 'application')); // , compact('')
    }

    public function modalspeItem($id)
    {
        $cons = IpConsignmentPermit::with(['attachments'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $cons,
        ]);
    }

    public function getApplicationDetails($id)
    {
        $type = authUser()['type']; // 'public' or 'internal'
        $user = authUser()['user']; // authenticated user object

        // Fetch application and eager load relationships
        $application = IpApplication::where('application_id', $id)
            ->with([
                'user',
                'importer',
                'exporter.countryInfo',
                'attachment',
                'entryPoint.districtCode',
                'consignmentPermits.attachments',
                'activity_log.causer'
            ])
            ->firstOrFail();

        // Collect QR scan logs linked to this import permit application via its order numbers.
        $orderNumbers = Order::query()
            ->where('application_id', $application->application_id)
            ->where('application_type', 'Import Permit')
            ->pluck('order_number')
            ->filter()
            ->values();

        $qrScanLogs = collect();
        if ($orderNumbers->isNotEmpty()) {
            $qrScanLogs = QrScanLog::query()
                ->where('application_type', 'Import Permit')
                ->whereIn('order_number', $orderNumbers)
                ->where(function ($query) {
                    $query->whereRaw("LOWER(COALESCE(result, '')) IN (?, ?, ?, ?)", ['approved', 'valid', 'used', 'rejected'])
                        ->orWhere(function ($legacyQuery) {
                            $legacyQuery->whereNull('result')
                                ->where('is_valid', true);
                        });
                })
                ->latest('scanned_at')
                ->get()
                ->map(function ($log) {
                    return [
                        'internal_user_name' => $log->internal_user_name ?? '-',
                        'internal_user_position' => $log->internal_user_position ?? '-',
                        'scanned_value' => $log->scanned_value ?? '-',
                        'is_valid' => (bool) $log->is_valid,
                        'result' => ucfirst((string) ($log->result ?: 'invalid')),
                        'scanned_at' => optional($log->scanned_at)->toIso8601String(),
                    ];
                })
                ->values();
        }

        $application->setAttribute('qr_scan_logs', $qrScanLogs);

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

    public function verify_application_permit($id, Request $request)
    {
        $application = IpApplication::where('application_id', $id)->firstOrFail();

        // Centralized messages per status
        $statusMessages = [
            'Clerk Review In-Progress' => [
                'public' => 'Your application has been verified by the importer and is now pending clerk review.',
                'public_bm' => 'Permohonan anda telah disahkan oleh pengimport dan kini menunggu semakan kerani.',
                'internal' => 'Application verified by importer and awaiting clerk review.',
                'notify' => 'Import application is now awaiting clerk review.',
                'notify_bm' => 'Permohonan import kini menunggu semakan kerani.',
            ],
            'Not Approved' => [
                'public' => 'Your application was not approved by the importer.',
                'public_bm' => 'Permohonan anda tidak diluluskan oleh pengimport.',
                'internal' => 'Application was not approved by the importer.',
                'notify' => 'Import application was not approved by the importer.',
                'notify_bm' => 'Permohonan import tidak diluluskan oleh pengimport.',
            ],
            'Clerk Verified' => [
                'public' => 'Your application has been approved by the clerk.',
                'public_bm' => 'Permohonan anda telah diluluskan oleh kerani.',
                'internal' => 'Application approved by clerk.',
                'notify' => 'Import application has been approved by clerk.',
                'notify_bm' => 'Permohonan import telah diluluskan oleh kerani.',
            ],
            'Clerk Rejected' => [
                'public' => 'Your application has been rejected by the clerk.',
                'public_bm' => 'Permohonan anda telah ditolak oleh kerani.',
                'internal' => 'Application rejected by clerk.',
                'notify' => 'Import application has been rejected by clerk.',
                'notify_bm' => 'Permohonan import telah ditolak oleh kerani.',
            ],
        ];

        $status = null;

        /**
         * =====================
         * STATUS HANDLING
         * =====================
         */
        if ($request->input('verified')) {
            $application->logActivity(action: 'Importer Verified', remark: 'Application verified by importer', status: 'Clerk Review In-Progress');

            $application->status = 'Clerk Review In-Progress';
            $application->importer_verify = 'Verified';
            $status = 'Clerk Review In-Progress';

            ApplicationActivityLogger::log(
                application: $application,
                event: 'importer_verified',
                description: authUser()['user']->fullname . " verified application with id {$application->application_id}",
                properties: [
                    'role' => 'importer',
                ],
            );
        } elseif ($request->input('not_verified')) {
            $application->logActivity(action: 'Importer Rejected', remark: 'Application rejected by importer', status: 'Not Approved');

            $application->status = 'Not Approved';
            $application->importer_verify = 'Not Approved';
            $status = 'Not Approved';

            ApplicationActivityLogger::log(
                application: $application,
                event: 'importer_verified',
                description: authUser()['user']->fullname . " not verified application with id {$application->application_id}",
                properties: [
                    'role' => 'importer',
                ],
            );
        } elseif ($request->accepted) {
            $application->logActivity(action: 'Clerk Approved', remark: 'Application approved by clerk', status: 'Clerk Verified');

            $application->status = 'Clerk Verified';
            $application->importer_verify = 'Accepted';
            $status = 'Clerk Verified';

            ApplicationActivityLogger::log(
                application: $application,
                event: 'clerk_verified',
                description: authUser()['user']->fullname . " verified application {$application->application_id}",
                properties: [
                    'role' => 'clerk',
                ],
            );

            $notificationController = new NotificationController();

            $notificationController->sendStatusMessage(
                $application->importer_detail['fullname'] ?? 'User',
                'Import Permit',
                $application->application_id,
                'accepted by DOA',
                'Your application is under review and will be processed shortly',
                $application->importer->phone_number ?? '+60143290092', // recipient number
            );
        } elseif ($request->rejected) {
            $application->logActivity(action: 'Clerk Rejected', remark: $request->input('reason'), status: 'Clerk Rejected');

            $application->status = 'Clerk Rejected';
            $status = 'Clerk Rejected';

            ApplicationActivityLogger::log(
                application: $application,
                event: 'clerk_verified',
                description: authUser()['user']->fullname . " not verified application with id {$application->application_id} with reason " . $request->input('reason') . ' .',
                properties: [
                    'role' => 'clerk',
                ],
            );
        }

        // Save application state
        $application->save();

        // Safety check
        if (!$status || !isset($statusMessages[$status])) {
            return response()->json(
                [
                    'message' => 'Invalid application status.',
                ],
                400,
            );
        }

        $messages = $statusMessages[$status];
        $notificationUrl = route('viewApplication', $application->application_id);

        /**
         * =====================
         * INTERNAL USER EVENT + NOTIFICATION
         * =====================
         */
        try {
            event(new ApplicationCreatedInternalUser($messages['internal']));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed but continuing internal notification: ' . $e->getMessage());
        }

        $internalUsers = InternalUser::all();
        Notification::send($internalUsers, new ApplicationNotification($messages['notify'], $messages['notify_bm'], authUser()['user']->fullname ?? 'System', $notificationUrl));

        // If status became Clerk Verified, dispatch the email with buttons to Officers
        if (strtolower($status) === 'clerk verified') {
            $officerUsers = InternalUser::permission('approve permit')->get();
            Notification::send($officerUsers, new \App\Notifications\ApplicationSubmittedNotification(
                'Application verified by clerk and awaits officer verification',
                'Permohonan telah disahkan oleh kerani dan menunggu pengesahan pegawai',
                authUser()['user']->fullname ?? 'System',
                $notificationUrl,
                $application->application_id
            ));
        }

        /**
         * =====================
         * PUBLIC USER (APPLICANT)
         * =====================
         */
        $publicUser = PublicUser::where('uuid', $application->user_id)->first();

        try {
            event(new ApplicationCreatedPublicUser($messages['public'], $publicUser->uuid));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed but continuing public notification: ' . $e->getMessage());
        }

        Notification::send($publicUser, new ApplicationNotification($messages['public'], $messages['public_bm'], authUser()['user']->fullname ?? 'System', $notificationUrl));

        /**
         * =====================
         * IMPORTER (IF DIFFERENT USER)
         * =====================
         */
        if ($application->importer_id !== $application->user_id) {
            $importerUser = PublicUser::where('uuid', $application->importer_id)->first();

            try {
                event(new ApplicationCreatedPublicUser($messages['public'], $importerUser->uuid));
            } catch (\Exception $e) {
                Log::warning('Pusher connection failed but continuing importer notification: ' . $e->getMessage());
            }
            Notification::send($importerUser, new ApplicationNotification($messages['public'], $messages['public_bm'], authUser()['user']->fullname ?? 'System', $notificationUrl));
        }

        /**
         * =====================
         * RESPONSE
         * =====================
         */
        return response()->json([
            'message' => 'Application status updated successfully.',
            'status' => $status,
        ]);
    }

    public function handleEmailAction($id, $action, Request $request)
    {
        $application = IpApplication::where('application_id', $id)->first();
        if (!$application) {
            $application = \App\Models\ConsignmentApplication::where('application_id', $id)->first();
        }
        if (!$application) {
            $application = \App\Models\InspectionApplication::where('application_id', $id)->first();
        }
        if (!$application) {
            return redirect('/')->with('error', 'Application not found.');
        }

        if (!authUser()) {
            return redirect()->guest('/login')->with('error', 'Please login to perform this action.');
        }

        $type = authUser()['type'];
        if ($type !== 'internal') {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $user = authUser()['user'];
        $status = strtolower($application->status ?? '');

        if ($action === 'approve') {
            if (str_contains($status, 'clerk review in-progress')) {
                // Needs Clerk Approval
                if (!$user->hasPermissionTo('approve application')) {
                    return redirect('/')->with('error', 'You do not have permission to approve at this stage.');
                }
                $application->logActivity(action: 'Clerk Approved', remark: 'Application approved by clerk via email', status: 'Clerk Verified');
                $application->status = 'Clerk Verified';
                $application->importer_verify = 'Accepted';

                ApplicationActivityLogger::log(
                    application: $application,
                    event: 'clerk_verified',
                    description: $user->fullname . " verified application {$application->application_id} via email",
                    properties: ['role' => 'clerk']
                );

                // Dispatch email to Officers for next step
                $officerUsers = \App\Models\InternalUser::permission('approve permit')->get();
                $notificationUrl = '';
                if ($application instanceof \App\Models\ConsignmentApplication) {
                    $notificationUrl = route('consignment.view', $id);
                } elseif ($application instanceof \App\Models\InspectionApplication) {
                    $notificationUrl = route('inspection.view_details', $id);
                } else {
                    $notificationUrl = route('viewApplication', $id);
                }

                \Illuminate\Support\Facades\Notification::send($officerUsers, new \App\Notifications\ApplicationSubmittedNotification(
                    'Application verified by clerk and awaits officer verification',
                    'Permohonan telah disahkan oleh kerani dan menunggu pengesahan pegawai',
                    $user->fullname ?? 'System',
                    $notificationUrl,
                    $application->application_id
                ));
            } elseif (str_contains($status, 'clerk verified')) {
                // Needs Officer Approval
                if (!$user->hasPermissionTo('approve permit')) {
                    return redirect('/')->with('error', 'You do not have permission to approve at this stage.');
                }
                $application->logActivity(action: 'Officer Approved', remark: 'Application approved by officer via email', status: 'Officer Verification Completed');
                $application->status = 'Officer Verification Completed';

                ApplicationActivityLogger::log(
                    application: $application,
                    event: 'officer_verified',
                    description: $user->fullname . " verified application {$application->application_id} via email",
                    properties: ['role' => 'officer']
                );
            } else {
                return redirect('/')->with('error', 'This application cannot be approved in its current status: ' . $application->status);
            }
        } elseif ($action === 'reject') {
            $reason = 'Rejected via email';
            if (str_contains($status, 'clerk review in-progress')) {
                if (!$user->hasPermissionTo('approve application')) {
                    return redirect('/')->with('error', 'You do not have permission to reject at this stage.');
                }
                $application->logActivity(action: 'Clerk Rejected', remark: $reason, status: 'Clerk Rejected');
                $application->status = 'Clerk Rejected';

                ApplicationActivityLogger::log(
                    application: $application,
                    event: 'clerk_rejected',
                    description: $user->fullname . " rejected application {$application->application_id} via email",
                    properties: ['role' => 'clerk']
                );
            } elseif (str_contains($status, 'clerk verified')) {
                if (!$user->hasPermissionTo('approve permit')) {
                    return redirect('/')->with('error', 'You do not have permission to reject at this stage.');
                }
                $application->logActivity(action: 'Officer Rejected', remark: $reason, status: 'Rejected');
                $application->status = 'Rejected';

                ApplicationActivityLogger::log(
                    application: $application,
                    event: 'officer_rejected',
                    description: $user->fullname . " rejected application {$application->application_id} via email",
                    properties: ['role' => 'officer']
                );
            } else {
                return redirect('/')->with('error', 'This application cannot be rejected in its current status: ' . $application->status);
            }
        }

        $application->save();

        $route = 'viewApplication';
        if ($application instanceof \App\Models\ConsignmentApplication) {
            $route = 'consignment.view';
        } elseif ($application instanceof \App\Models\InspectionApplication) {
            $route = 'inspection.view_details';
        }

        return redirect(route($route, $application->application_id))
            ->with('success', 'Application ' . ucfirst($action) . 'd successfully.');
    }

    function show_exporter()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();
        return view('pages.public.exporter_list', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    function get_exporter($id)
    {
        $exporter = Exporter::with(['countryInfo'])->find($id);

        return response()->json([
            'exporter' => $exporter,
        ]);
    }

    function show_importer()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();
        return view('pages.public.importer_list', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    function get_importer($id)
    {
        $importer = ConsignmentImporter::with(['countryInfo'])->find($id);

        return response()->json([
            'importer' => $importer,
        ]);
    }

    public function application_count()
    {
        $reviewStatuses = ['processing', 'submitted', 'reapplied'];

        return response()->json([
            'permit'      => $this->countPermitLineItemsAwaitingReview(IpConsignmentPermit::class, $reviewStatuses),
            'inspection'  => $this->countPermitLineItemsAwaitingReview(InspectionItem::class, $reviewStatuses),
            'consignment' => $this->countPermitLineItemsAwaitingReview(ConsignmentPermit::class, $reviewStatuses),
        ]);
    }

    private function countPermitLineItemsAwaitingReview(string $modelClass, array $reviewStatuses): int
    {
        return $modelClass::query()
            ->whereRaw('LOWER(TRIM(COALESCE(status, ""))) IN (?, ?, ?)', $reviewStatuses)
            ->count();
    }

    // ======================= Internal Exporter & Importer Lists =======================

    public function showInternalExporterList()
    {
        Gate::authorize('view exporter list');
        return view('pages.internal.exporter_list');
    }

    public function getInternalExporterListData()
    {
        $user = authUser();
        $query = Exporter::with(['countryInfo', 'registeredBy']);

        // ─── Public users: only see their own registered exporters ───
        if ($user['type'] === 'public') {
            $query->where('registered_by', $user['user']->uuid);
        }
        // Internal users: see all (no additional where clause)

        // ─── Apply optional filters ──────────────────────────────────
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }
        if (request('country')) {
            $query->where('country', request('country'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('country_name', fn($row) => $row->countryInfo->name ?? '-')
            ->addColumn('registered_by_name', fn($row) => $row->registeredBy->fullname ?? '-')
            ->make(true);
    }

    public function showInternalImporterList()
    {
        Gate::authorize('view importer list');
        $country = Country::where('is_del', false)->get();
        return view('pages.internal.importer_list', compact('country'));
    }

    public function getInternalImporterListData()
    {
        $user = authUser();
        $query = ConsignmentImporter::with(['countryInfo', 'registeredBy']);

        // ─── Public users: only see their own registered importers ───
        if ($user['type'] === 'public') {
            $query->where('registered_by', $user['user']->uuid);
        }
        // Internal users: see all (no additional where clause)

        // ─── Apply optional filters ──────────────────────────────────
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }
        if (request('country')) {
            $query->where('country', request('country'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('country_name', fn($row) => $row->countryInfo->name ?? '-')
            ->addColumn('registered_by_name', fn($row) => $row->registeredBy->fullname ?? '-')
            ->make(true);
    }



    public function printImportPermit($id)
    {
        $application = IpApplication::with([
            'user',
            'exporter',
            'importer',
            'entryPoint',
            'consignmentPermits',
        ])->where('application_id', $id)->firstOrFail();

        $pdf = Pdf::loadView('pdf.import_permit_application', compact('application'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Import_Permit_Application_{$application->application_id}.pdf");
    }
}
