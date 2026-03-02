<?php

namespace App\Http\Controllers;

use App\Models\Exporter;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilterController extends Controller
{
    /**
     * Get exporters registered by the logged-in public user
     */
    public function getMyExporters()
    {
        $user = authUser()['user'];
        
        $exporters = Exporter::where('registered_by', $user->uuid)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($exporters);
    }

    /**
     * Get importers registered by the logged-in public user
     * Note: Based on the codebase, importers might use the same exporter table
     * or a different structure. Adjust accordingly.
     */
    public function getMyImporters()
    {
        $user = authUser()['user'];
        
        // Check if there's a separate Importer model or if importers are PublicUsers
        // From the application structure, importers appear to be PublicUsers
        $importers = PublicUser::where('uuid', '!=', $user->uuid)
            ->select('uuid as id', 'fullname as name')
            ->orderBy('fullname')
            ->get();

        return response()->json($importers);
    }

    /**
     * Get all public users (for internal users only)
     */
    public function getPublicUsers()
    {
        $publicUsers = PublicUser::select('uuid', 'fullname', 'email')
            ->orderBy('fullname')
            ->get();

        return response()->json($publicUsers);
    }

    /**
     * Get exporters registered by a specific user (for internal users only)
     */
    public function getUserExporters($userUuid)
    {
        $exporters = Exporter::where('registered_by', $userUuid)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($exporters);
    }

    /**
     * Get importers for a specific user (for internal users only)
     */
    public function getUserImporters($userUuid)
    {
        // Importers are PublicUsers excluding the user themselves
        $importers = PublicUser::where('uuid', '!=', $userUuid)
            ->select('uuid as id', 'fullname as name')
            ->orderBy('fullname')
            ->get();

        return response()->json($importers);
    }

    /**
     * Get all exporters for consignment (all PublicUsers who can be exporters)
     * For internal users only
     */
    public function getAllConsignmentExporters()
    {
        $exporters = PublicUser::select('uuid as id', 'fullname as name')
            ->orderBy('fullname')
            ->get();

        return response()->json($exporters);
    }

    /**
     * Get all consignment importers
     * For internal users only
     */
    public function getAllConsignmentImporters()
    {
        $importers = \App\Models\ConsignmentImporter::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($importers);
    }

    /**
     * Get exporters for a specific user in consignment context
     * For internal users only
     */
    public function getUserConsignmentExporters($userUuid)
    {
        // For consignment, exporter_id is a PublicUser UUID
        // Return the selected user as an exporter option
        $user = PublicUser::where('uuid', $userUuid)
            ->select('uuid as id', 'fullname as name')
            ->first();

        return response()->json($user ? [$user] : []);
    }

    /**
     * Get importers for a specific user in consignment context
     * For internal users only
     */
    public function getUserConsignmentImporters($userUuid)
    {
        // For consignment, importers are ConsignmentImporter records
        // We need to find importers associated with applications from this user
        $importerIds = \App\Models\ConsignmentApplication::where('user_id', $userUuid)
            ->orWhere('exporter_id', $userUuid)
            ->distinct()
            ->pluck('importer_id')
            ->filter();

        $importers = \App\Models\ConsignmentImporter::whereIn('id', $importerIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($importers);
    }

    /**
     * Get exporters for the logged-in public user in consignment context
     */
    public function getMyConsignmentExporters()
    {
        $user = authUser()['user'];
        // For consignment, the public user is their own exporter
        return response()->json([
            ['id' => $user->uuid, 'name' => $user->fullname]
        ]);
    }

    /**
     * Get importers for the logged-in public user in consignment context
     */
    public function getMyConsignmentImporters()
    {
        $user = authUser()['user'];
        // For consignment, importers are ConsignmentImporter records
        $importers = \App\Models\ConsignmentImporter::where('registered_by', $user->uuid)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($importers);
    }
}
