<?php

namespace App\Http\Controllers\internal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('pages.internal.announcement.announcement');
    }

    public function data(Request $request)
    {
        $query = Announcement::with('releasedBy')->latest();

        return DataTables::of($query)
            ->addColumn('released_by_name', function ($announcement) {
                return $announcement->releasedBy ? $announcement->releasedBy->fullname : 'Unknown';
            })
            ->make(true);
    }

    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);
        return response()->json($announcement);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'released_by' => auth('internal')->user()->uuid,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Announcement created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $announcement = Announcement::findOrFail($id);
        
        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Announcement updated successfully.']);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['status' => 'success', 'message' => 'Announcement deleted successfully.']);
    }

    public function toggleActive($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->is_active = !$announcement->is_active;
        $announcement->save();

        return response()->json(['status' => 'success', 'message' => 'Status updated successfully.']);
    }
}
