<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    /**
     * Display the gallery management page.
     */
    public function index()
    {
        return view('pages.internal.gallery.gallery');
    }

    /**
     * Return DataTable data for gallery management.
     */
    public function data()
    {
        $galleries = Gallery::with('releasedBy') // relationship to InternalUser
            ->select('galleries.*')
            ->orderBy('created_at', 'desc');

        return DataTables::of($galleries)
            ->addColumn('thumbnail', function ($gallery) {
                if ($gallery->path) {
                    return '<img src="' . asset('storage/' . $gallery->path) . '" style="max-height: 60px; max-width: 60px; object-fit: cover; border-radius: 4px;" alt="' . e($gallery->name) . '">';
                }
                return '<span class="text-muted">No image</span>';
            })
            ->addColumn('uploaded_by', function ($gallery) {
                return $gallery->user ? $gallery->user->fullname : '—';
            })
            ->addColumn('created_at', function ($gallery) {
                return $gallery->created_at ? $gallery->created_at->format('d M Y H:i') : '—';
            })
            ->addColumn('action', function ($gallery) {
                return $gallery->id;
            })
            ->rawColumns(['thumbnail', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created gallery.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $user = auth('internal')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Store the image
        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('galleries', $filename, 'public');

        $gallery = Gallery::create([
            'user_id'     => $user->uuid,
            'name'        => $request->name,
            'path'        => $path,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Gallery image added successfully.',
            'id'      => $gallery->id,
        ], 201);
    }

    /**
     * Display the specified gallery (for view modal).
     */
    public function show($id)
    {
        $gallery = Gallery::findOrFail($id);
        return response()->json($gallery);
    }

    /**
     * Update the specified gallery.
     */
    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = [
            'name'        => $request->name,
            'description' => $request->description,
        ];

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image
            if ($gallery->path && Storage::disk('public')->exists($gallery->path)) {
                Storage::disk('public')->delete($gallery->path);
            }

            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('galleries', $filename, 'public');
            $data['path'] = $path;
        }

        $gallery->update($data);

        return response()->json([
            'message' => 'Gallery image updated successfully.',
        ]);
    }

    /**
     * Remove the specified gallery.
     */
    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);

        // Delete the image file
        if ($gallery->path && Storage::disk('public')->exists($gallery->path)) {
            Storage::disk('public')->delete($gallery->path);
        }

        $gallery->delete();

        return response()->json([
            'message' => 'Gallery image deleted successfully.',
        ]);
    }
}