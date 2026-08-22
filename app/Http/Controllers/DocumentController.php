<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequirement;
use App\Models\UserAttachment;
use Illuminate\Http\Request;
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
}
