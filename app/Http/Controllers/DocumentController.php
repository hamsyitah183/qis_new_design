<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentApplicationAttachment;
use App\Models\DocumentRequirement;
use App\Models\InspectionApplicationAttachment;
use App\Models\IpApplicationAttachment;
use App\Models\UserAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Display the document requirements list page.
     */
    public function index()
    {
        return view('pages.internal.documents.list');
    }

    public function create()
    {
        return view('pages.internal.documents.add');
    }

    public function edit($id)
    {
        $document = DocumentRequirement::findOrFail($id);

        return view('pages.internal.documents.edit', compact('document'));
    }

    /**
     * Return DataTable data for document requirements.
     */
    public function data()
    {
        $documents = DocumentRequirement::select('*')->orderBy('module')->orderBy('name');

        return DataTables::of($documents)
            ->addColumn('status_badge', function ($doc) {
                $class = $doc->is_active ? 'success' : 'danger';
                $labelEn = $doc->is_active ? 'Active' : 'Inactive';
                $labelBm = $doc->is_active ? 'Aktif' : 'Tidak Aktif';
                return '<span class="badge bg-' . $class . '" data-en="' . $labelEn . '" data-bm="' . $labelBm . '">' . $labelEn . '</span>';
            })
            ->addColumn('required_badge', function ($doc) {
                return $doc->is_required
                    ? '<span class="badge bg-warning text-dark" data-en="Required" data-bm="Wajib">Required</span>'
                    : '<span class="badge bg-secondary" data-en="Optional" data-bm="Pilihan">Optional</span>';
            })
            ->addColumn('expiry_badge', function ($doc) {
                return $doc->requires_expiry
                    ? '<span class="badge bg-info" data-en="Has Expiry" data-bm="Tarikh Luput">Has Expiry</span>'
                    : '<span class="badge bg-secondary" data-en="No Expiry" data-bm="Tiada Tarikh Luput">No Expiry</span>';
            })
            ->addColumn('action', function ($doc) {
                return $doc->id;
            })
            ->rawColumns(['status_badge', 'required_badge', 'expiry_badge', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created document requirement.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module'          => 'required|string|max:50',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'is_required'     => 'boolean',
            'requires_expiry' => 'boolean',
            'is_active'       => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $document = DocumentRequirement::create([
            'module'          => $request->module,
            'name'            => $request->name,
            'description'     => $request->description,
            'is_required'     => $request->is_required ?? false,
            'requires_expiry' => $request->requires_expiry ?? false,
            'is_active'       => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Document requirement added successfully.',
            'id'      => $document->id,
        ], 201);
    }

    /**
     * Display the specified document requirement (for edit/view modal).
     */
    public function show($id)
    {
        $document = DocumentRequirement::findOrFail($id);
        return response()->json($document);
    }

    /**
     * Update the specified document requirement.
     */
    public function update(Request $request, $id)
    {
        $document = DocumentRequirement::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'module'          => 'required|string|max:50',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'is_required'     => 'boolean',
            'requires_expiry' => 'boolean',
            'is_active'       => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $document->update([
            'module'          => $request->module,
            'name'            => $request->name,
            'description'     => $request->description,
            'is_required'     => $request->is_required ?? false,
            'requires_expiry' => $request->requires_expiry ?? false,
            'is_active'       => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Document requirement updated successfully.',
        ]);
    }

    /**
     * Remove the specified document requirement.
     */
    public function destroy($id)
    {
        $document = DocumentRequirement::findOrFail($id);
        $document->delete();

        return response()->json([
            'message' => 'Document requirement deleted successfully.',
        ]);
    }

    /**
     * Get documents by module (for public API).
     */
    public function getByModule(Request $request)
    {
        $module = $request->input('module', 'user');
        $documents = DocumentRequirement::where('module', $module)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $documents,
        ]);
    }



    function documentsList()
    {
        $document =  DocumentRequirement::where('is_active', 1)
            ->where('module', 'user')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 200,
            'document' => $document
        ]);
    }

    // Add method
    public function showView($id)
    {
        $document = DocumentRequirement::findOrFail($id);
        return view('pages.internal.documents.view', compact('document'));
    }

    /**
     * Return DataTable data for user attachments of a specific document.
     */
    public function attachmentsData($id)
    {
        $document = DocumentRequirement::findOrFail($id);

        if ($document->module == 'user') {
            $attachments = UserAttachment::where('document_type', $document->name)
                ->with('user')
                ->orderBy('created_at', 'desc');
        } elseif ($document->module == 'import') {
            $attachments = IpApplicationAttachment::where('description', $document->name)
                ->with('application.user')
                ->orderBy('created_at', 'desc');
        } elseif ($document->module == 'consignment') {
            $attachments = ConsignmentApplicationAttachment::where('description', $document->name)
                ->with('application.exporter') // exporter is the applicant for consignment
                ->orderBy('created_at', 'desc');
        } elseif ($document->module == 'inspection') {
            $attachments = InspectionApplicationAttachment::where('description', $document->name)
                ->with('application.user')
                ->orderBy('created_at', 'desc');
        }

        return DataTables::of($attachments)
            ->addColumn('user_name', function ($att) {
                // Direct user relation (UserAttachment)
                if (method_exists($att, 'user') && $att->relationLoaded('user')) {
                    return $att->user ? $att->user->fullname : '—';
                }

                // Through application relationship
                if (method_exists($att, 'application') && $att->relationLoaded('application')) {
                    $app = $att->application;
                    if ($app) {
                        // Try user (import/inspection/public)
                        if (method_exists($app, 'user') && $app->relationLoaded('user')) {
                            return $app->user ? $app->user->fullname : '—';
                        }
                        // Try exporter (consignment)
                        if (method_exists($app, 'exporter') && $app->relationLoaded('exporter')) {
                            return $app->exporter ? $app->exporter->fullname : '—';
                        }
                        // Try importer (import permit)
                        if (method_exists($app, 'importer') && $app->relationLoaded('importer')) {
                            return $app->importer ? $app->importer->fullname : '—';
                        }
                    }
                }
                return '—';
            })

            ->addColumn('file_name_display', function ($att) use ($document) {
                // ✅ original_file_name exists ONLY on UserAttachment
                if ($document->module == 'user' && isset($att->original_file_name) && $att->original_file_name) {
                    return $att->original_file_name;
                }
                // file_name is common across all attachment models
                if (isset($att->file_name) && $att->file_name) {
                    return $att->file_name;
                }
                if (isset($att->name) && $att->name) {
                    return $att->name;
                }
                return '—';
            })

            ->addColumn('file_size_formatted', function ($att) {
                if (isset($att->file_size) && $att->file_size) {
                    return number_format($att->file_size / 1024, 2) . ' KB';
                }
                return '—';
            })

            ->addColumn('valid_from_formatted', function ($att) use ($document) {
                // ✅ Only UserAttachment has valid_from/valid_until
                if ($document->module == 'user' && isset($att->valid_from)) {
                    return $att->valid_from ? $att->valid_from->format('d M Y') : '—';
                }
                return '—';
            })

            ->addColumn('valid_until_formatted', function ($att) use ($document) {
                if ($document->module == 'user' && isset($att->valid_until)) {
                    return $att->valid_until ? $att->valid_until->format('d M Y') : '—';
                }
                return '—';
            })

            ->addColumn('is_read_badge', function ($att) {
                if (isset($att->is_read)) {
                    return $att->is_read
                        ? '<span class="badge bg-success-transparent" data-en="Read" data-bm="Dibaca">Read</span>'
                        : '<span class="badge bg-warning-transparent" data-en="Unread" data-bm="Belum Dibaca">Unread</span>';
                }
                return '<span class="text-muted">—</span>';
            })

            ->addColumn('rejected_reason_button', function ($att) {
                if (isset($att->rejected_reason) && !empty($att->rejected_reason)) {
                    $reason = htmlspecialchars($att->rejected_reason, ENT_QUOTES);
                    return '<button type="button" class="btn btn-sm btn-danger-light view-reject-reason-btn" data-reason="' . $reason . '">
                    <i class="ti ti-alert-circle"></i> Reason
                </button>';
                }
                return '<span class="text-muted">—</span>';
            })

            ->addColumn('action', function ($att) {
                return $att->id;
            })

            ->rawColumns(['is_read_badge', 'rejected_reason_button', 'action'])
            ->make(true);
    }
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $isImage = str_starts_with($mime, 'image/');

        // Store in the private temp disk, NOT public
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('', $filename, 'temp-uploads');

        return response()->json([
            // Served through a controlled preview route, not a direct public path
            'url'      => route('internal.documents.temp-preview', ['filename' => $filename]),
            'temp_key' => $filename,
            'is_image' => $isImage,
            'name'     => $file->getClientOriginalName(),
            'size'     => $file->getSize(),
        ]);
    }

    /**
     * Stream a temp-uploaded file back for preview while editing.
     */
    public function tempPreview($filename)
    {
        $path = storage_path('app/temp-uploads/' . basename($filename));

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    /**
     * Move any temp-uploaded files referenced in the HTML into permanent public storage,
     * and rewrite the HTML to point at the final URLs.
     */
    private function promoteTempFiles(string $html): string
    {
        // Match our temp-preview route URLs used as src="" or href=""
        return preg_replace_callback(
            '/(?:src|href)="([^"]*documents\/temp-preview\/([a-zA-Z0-9._-]+))"/',
            function ($matches) {
                $fullUrl = $matches[1];
                $filename = $matches[2];

                $tempPath = storage_path('app/temp-uploads/' . $filename);

                if (!file_exists($tempPath)) {
                    return $matches[0]; // leave untouched if already gone
                }

                $contents = file_get_contents($tempPath);
                $permanentPath = 'document-descriptions/' . $filename;
                Storage::disk('public')->put($permanentPath, $contents);

                @unlink($tempPath);

                $permanentUrl = Storage::url($permanentPath);

                return str_replace($fullUrl, $permanentUrl, $matches[0]);
            },
            $html
        );
    }
}
