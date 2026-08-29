<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequirement;
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
                $label = $doc->is_active ? 'Active' : 'Inactive';
                return '<span class="badge bg-' . $class . '">' . $label . '</span>';
            })
            ->addColumn('required_badge', function ($doc) {
                return $doc->is_required
                    ? '<span class="badge bg-warning text-dark">Required</span>'
                    : '<span class="badge bg-secondary">Optional</span>';
            })
            ->addColumn('expiry_badge', function ($doc) {
                return $doc->requires_expiry
                    ? '<span class="badge bg-info">Has Expiry</span>'
                    : '<span class="badge bg-secondary">No Expiry</span>';
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
        $attachments = UserAttachment::where('document_type', $document->name)
            ->with('user')
            ->orderBy('created_at', 'desc');

        return DataTables::of($attachments)
            ->addColumn('user_name', function ($att) {
                return $att->user ? $att->user->fullname : '—';
            })
            ->addColumn('file_size_formatted', function ($att) {
                return $att->file_size ? number_format($att->file_size / 1024, 2) . ' KB' : '—';
            })
            ->addColumn('valid_from_formatted', function ($att) {
                return $att->valid_from ? $att->valid_from->format('d M Y') : '—';
            })
            ->addColumn('valid_until_formatted', function ($att) {
                return $att->valid_until ? $att->valid_until->format('d M Y') : '—';
            })
            ->addColumn('action', function ($att) {
                return '<a href="' . asset('storage/' . $att->file_path) . '" target="_blank" class="btn btn-sm btn-primary">Download</a>';
            })
            ->rawColumns(['action'])
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
