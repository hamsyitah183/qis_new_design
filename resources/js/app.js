import './bootstrap';
import { IconHome, IconUser } from 'tabler-icons';
import '@fortawesome/fontawesome-free/css/all.min.css';
// import './feather-icons';

import $ from 'jquery';
window.$ = window.jQuery = $; // make it global


// $.ajaxSetup({
//     headers: {
//         "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
//     },
// });

$('#redirectProfile').on('click', function(e) {
    e.preventDefault();

    console.log('redirect ');

    window.location.href = '/profile'
})