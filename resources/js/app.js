import './bootstrap';
import { IconHome, IconUser } from 'tabler-icons';
import '@fortawesome/fontawesome-free/css/all.min.css';
// import './feather-icons';

// If you are using JavaScript/ECMAScript modules:


// // If you are using an older version than Dropzone 6.0.0,
// // then you need to disabled the autoDiscover behaviour here:
// Dropzone.autoDiscover = false;

// let myDropzone = new Dropzone("#my-form");
// myDropzone.on("addedfile", file => {
//   console.log(`File added: ${file.name}`);
// });

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