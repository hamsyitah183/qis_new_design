import Dropzone from "dropzone";
import $ from "jquery";
import Swal from "sweetalert2";
import "dropzone/dist/dropzone.css";
import { loadProfile } from "./profile";

Dropzone.autoDiscover = false;

let attachmentVar = []; // ✅ Will contain files currently in queue or uploaded

const dzElement = document.querySelector("#verificationDropzone");

// If already initialized → destroy it
if (dzElement.dropzone) {
    dzElement.dropzone.destroy();
}



// Initialize Dropzone
const verificationDropzone = new Dropzone("#verificationDropzone", {
    url: "/public/upload-verification",
    autoProcessQueue: false,
    paramName: "attachment",
    maxFilesize: 5,
    maxFiles: 1,
    acceptedFiles: ".jpg,.jpeg,.png,.pdf",
    addRemoveLinks: true,
    dictDefaultMessage: "Drop your verification file here or click to upload.",
    headers: {
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
});

// When a file is added
verificationDropzone.on("addedfile", function (file) {
    // Always keep only the latest file
    if (attachmentVar.length >= 1) {
        const oldFile = attachmentVar[0].fileObj;
        verificationDropzone.removeFile(oldFile);
        attachmentVar = [];
    }

    attachmentVar.push({
        name: file.name,
        size: file.size,
        type: file.type,
        fileObj: file,
    });

    console.log("File added to attachmentVar:", attachmentVar);
});

// When a file is removed manually
verificationDropzone.on("removedfile", function (file) {
    attachmentVar = attachmentVar.filter(f => f.name !== file.name);
    console.log("File removed from attachmentVar:", attachmentVar);
});

// Upload button click
$("#uploadBtn").on("click", function () {
    const userId = $(this).data("id");

    if (attachmentVar.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "No file selected",
            text: "Please add a file before uploading.",
        });
        return;
    }

    // Attach user_id before sending
    verificationDropzone.on("sending", function (file, xhr, formData) {
        formData.append("user_id", userId);
    });

    verificationDropzone.processQueue();
});

// On successful upload
verificationDropzone.on("success", function (file, response) {
    Swal.fire({
        icon: "success",
        title: "Uploaded!",
        text: response.message,
    });

    // Update the URL in attachmentVar
    const uploaded = attachmentVar.find(f => f.name === file.name);
    if (uploaded) uploaded.url = response.file_url;

    loadProfile();

    console.log("Upload success — updated attachmentVar:", attachmentVar);

    // Clear queue on success
    attachmentVar = [];
    verificationDropzone.removeAllFiles(true);
});

// On upload error
verificationDropzone.on("error", function (file, errorMessage) {
    Swal.fire({
        icon: "error",
        title: "Upload Failed",
        text: typeof errorMessage === "string" ? errorMessage : errorMessage.message || "Something went wrong.",
    });

    // Keep the latest file in attachmentVar for retry
    // Do not clear attachmentVar or remove file
    console.log("Upload failed — attachmentVar still contains:", attachmentVar);
});
