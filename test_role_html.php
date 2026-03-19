<?php
$rs = new \App\Services\RoleService();
$req = \Illuminate\Http\Request::create("/internal/roles/list/data");
app()->instance("request", $req);
$dataTable = $rs->roleDataTable();
$json = json_decode($dataTable->getContent(), true);

if (isset($json['data'])) {
    foreach ($json['data'] as $row) {
        if (strpos($row['name'], 'boundary') !== false) {
            echo "--- HTML OUTPUT FOR BOUNDARY OFFICER ---\n";
            echo $row['permissions'] . "\n";
        }
    }
}
