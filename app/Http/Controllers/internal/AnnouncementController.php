<?php

namespace App\Http\Controllers\internal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $announcement = Announcement::with('attachments')->findOrFail($id);
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

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'released_by' => auth('internal')->user()->uuid,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Announcement created successfully.', 'id' => $announcement->id]);
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

        return response()->json(['status' => 'success', 'message' => 'Announcement updated successfully.', 'id' => $announcement->id]);
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

    // --- Attachment Methods ---

    public function getAttachments($id)
    {
        $attachments = AnnouncementAttachment::where('announcement_id', $id)->get();
        return response()->json($attachments);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $request->validate([
            'attachments.*' => 'required|image',
        ]);

        $announcement = Announcement::findOrFail($id);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('announcements/' . $announcement->id, $fileName, 'public');

                AnnouncementAttachment::create([
                    'announcement_id' => $announcement->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'uploaded_by' => auth('internal')->user()->uuid,
                ]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Attachments uploaded successfully.']);
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = AnnouncementAttachment::findOrFail($attachmentId);
        
        if (Storage::exists('public/' . $attachment->file_path)) {
            Storage::delete('public/' . $attachment->file_path);
        }
        
        $attachment->delete();

        return response()->json(['status' => 'success', 'message' => 'Attachment deleted successfully.']);
    }
}
