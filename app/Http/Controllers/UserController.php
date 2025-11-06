<?php

namespace App\Http\Controllers;

use App\Models\PublicUser;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    //
    public function public_list()
    {
        return view('pages.internal.user_management.list_public');
    }

    public function public_list_data()
    {
        $users = PublicUser::query();

        return DataTables::of($users)
            ->addColumn('action', function ($user) {
                $verifyBtn = $user->email_verified_at
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<button class="btn btn-sm btn-primary verify-btn" data-id="' . $user->id . '">Verify</button>';

                return $verifyBtn;
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at->format('d-m-Y H:i');
            })
            ->editColumn('account_type', function ($user) {
                return ucfirst($user->account_type);
            })
            ->editColumn('doa_verified', function ($user) {
                return $user->doa_verified ? 'Yes' : 'No';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
