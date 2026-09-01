<?php

namespace App\Http\Controllers\internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IpUses;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class IpUsesController extends Controller
{
    /**
     * Get data for DataTables
     */
    public function getData()
    {
        $uses = IpUses::select(['id', 'name']);
        
        return DataTables::of($uses)
            ->addColumn('action', function ($row) {
                return '
                    <div class="hstack gap-2 flex-wrap">
                        <a href="javascript:void(0);" class="text-info fs-14 lh-1 edit-ipuse-btn" 
                            data-id="' . $row->id . '" 
                            data-name="' . htmlspecialchars($row->name) . '">
                            <i class="ri-edit-line"></i>
                        </a>
                        <a href="javascript:void(0);" class="text-danger fs-14 lh-1 delete-ipuse-btn" 
                            data-id="' . $row->id . '">
                            <i class="ri-delete-bin-5-line"></i>
                        </a>
                    </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a newly created or updated resource in storage.
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('id') && !empty($request->id)) {
            $ipUse = IpUses::find($request->id);
            if (!$ipUse) {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }
            $ipUse->name = $request->name;
            $ipUse->save();
            $message = 'IP Uses updated successfully.';
        } else {
            IpUses::create([
                'name' => $request->name,
            ]);
            $message = 'IP Uses added successfully.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ipUse = IpUses::find($id);
        if (!$ipUse) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        }

        $ipUse->delete();

        return response()->json(['success' => true, 'message' => 'IP Uses deleted successfully.']);
    }
}
