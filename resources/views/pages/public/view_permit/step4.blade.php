 <div class="wizard-step" data-title="APPLICATION STATUS" data-id="dOM0iRAyJXsLTr9b3KZfQ2jNv4pgn6Gu" data-limit="3"
     data-step="4">
     <div class="row">
         <div class="col-xl-12">
             <div class="tab-pane active show" id="finish" role="tabpanel">
                 <div class="row d-flex justify-content-center">
                     <div class="col-lg-10">
                         @if ($application->importer_id == auth()->id())
                             <div class="row justify-content-center">
                                 <div class="col-auto d-flex gap-3">
                                     <button id="rejectAppl" type="button" class="btn btn-md btn-warning">Reject
                                         Application</button>
                                     <button id="verifyAppl" type="button" class="btn btn-md btn-info">Verify
                                         Application</button>
                                 </div>
                             </div>
                         @else
                             @if (
                                 $application->category_application == 0 ||
                                     ($application->category_application == 1 && $application->importer_verify == true))
                                 <div class="text-center p-4">
                                     <span class="avatar avatar-xl avatar-rounded bg-success-transparent svg-success">
                                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                             <rect width="256" height="256" fill="none">
                                             </rect>
                                             <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                                             <polyline points="88 136 112 160 168 104" fill="none"
                                                 stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                 stroke-width="16"></polyline>
                                             <circle cx="128" cy="128" r="96" fill="none"
                                                 stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                 stroke-width="16"></circle>
                                         </svg>
                                     </span>
                                     <h3 class="mt-2">Successful</h3>
                                     <p>Your permit application has successfully submitted.</p>
                                 </div>
                             @else
                                 <div class="text-center p-4">
                                     <span class="avatar avatar-xl avatar-rounded bg-warning-transparent svg-warning">
                                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
                                             <rect width="256" height="256" fill="none">
                                             </rect>
                                             <circle cx="128" cy="128" r="96" opacity="0.2"></circle>
                                             <line x1="128" y1="80" x2="128" y2="136"
                                                 stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                 stroke-width="16">
                                             </line>
                                             <circle cx="128" cy="172" r="12" fill="currentColor"></circle>
                                             <circle cx="128" cy="128" r="96" fill="none"
                                                 stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                 stroke-width="16"></circle>
                                         </svg>
                                     </span>
                                     <h3 class="mt-2">Pending</h3>
                                     <p>Your permit application is currently pending verification
                                         by the respective Importer.</p>
                                 </div>
                             @endif
                         @endif
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
