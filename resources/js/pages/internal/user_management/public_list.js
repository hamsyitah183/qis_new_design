
import DataTable from 'datatables.net-dt';
// import 'datatables.net-dt/css/dataTables.dataTables.css';


import Swal from 'sweetalert2';

// Initialize DataTable without jQuery
const table = new DataTable('#publicUsersTable', {
    processing: true,
    serverSide: true,
    ajax: "/internal/user_public/list/data",
    columns: [
        { data: "fullname", name: "fullname" },       // Name
        { data: "account_type", name: "account_type" }, // Account Type
        { data: "email", name: "email" },             // Email
        { data: "phone_number", name: "phone_number" }, // Phone Number
        { data: "created_at", name: "created_at" },   // Created At
        { data: "doa_verified", name: "doa_verified", orderable: false, searchable: false }, // Verified
        { data: "action", name: "action", orderable: false, searchable: false }, // Action
    ],

    responsive: !0,
});
