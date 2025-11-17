import Dropzone from "dropzone";
import $ from "jquery";
import Swal from "sweetalert2";
import "dropzone/dist/dropzone.css";
import { loadProfile } from "./profile";

Dropzone.autoDiscover = false;

let attachmentVar = []; // ✅ Will contain files currently in queue or uploaded


// Initialize Dropzone
const verificationDropzone = new Dropzone("#verificationDropzone", {
    url: "/public/upload-verification",
    autoProcessQueue: false,
    paramName: "attachment",
    maxFilesize: 5,
    maxFiles: 1,
    // data: {
    //     uuid: user_id
    // },
    acceptedFiles: ".jpg,.jpeg,.png,.pdf",
    addRemoveLinks: true,
    dictDefaultMessage: "Drop your verification file here or click to upload.",
    headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
});

// ✅ When a file is added to Dropzone
verificationDropzone.on("addedfile", function (file) {
    if (attachmentVar.length >= 1) {
        Swal.fire({
            icon: "error",
            title: "Upload Failed",
            text: "Only one file can be uploaded.",
        });
        verificationDropzone.removeFile(file); // prevent adding more
        return;
    }

    attachmentVar.push({
        name: file.name,
        size: file.size,
        type: file.type,
        fileObj: file, // keep reference to Dropzone file object
    });

    console.log("File added to attachmentVar:", attachmentVar);
});

// ✅ When a file is removed from Dropzone
verificationDropzone.on("removedfile", function (file) {
    attachmentVar = attachmentVar.filter(f => f.name !== file.name);
    console.log("File removed from attachmentVar:", attachmentVar);
});

// ✅ When user clicks upload button
$("#uploadBtn").on("click", function () {
    let userId = $(this).data('id'); // get user_id from button

    console.log('user id', userId);

    if (verificationDropzone.getQueuedFiles().length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No file selected",
            text: "Please add a file before uploading.",
        });
        return;
    }

    // Add user_id to each file upload
    verificationDropzone.on("sending", function (file, xhr, formData) {
        formData.append("user_id", userId); // ✅ append user_id to request
    });

    verificationDropzone.processQueue(); // start upload
});


// ✅ When upload succeeds
verificationDropzone.on("success", function (file, response) {
    Swal.fire({
        icon: "success",
        title: "Uploaded!",
        text: response.message,
    });

    // Update the URL in attachmentVar for the uploaded file
    const uploaded = attachmentVar.find(f => f.name === file.name);
    if (uploaded) uploaded.url = response.file_url;


    loadProfile();

    console.log("Upload success — updated attachmentVar:", attachmentVar);

    attachmentVar = [];
});

// ✅ When upload fails
verificationDropzone.on("error", function (file, errorMessage) {
    Swal.fire({
        icon: "error",
        title: "Upload Failed",
        text:
            typeof errorMessage === "string"
                ? errorMessage
                : errorMessage.message || "Something went wrong.",
    });
});
