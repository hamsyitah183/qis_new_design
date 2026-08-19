<?php
return [
    // For internal users (admin/clerk)
    'internal_draft_created'  => 'New import permit draft created',
    'internal_draft_updated'  => 'Import permit draft updated',
    'internal_submit_created' => 'New import permit application submitted',
    'internal_submit_updated' => 'Import permit application updated',

    // For public applicant
    'public_draft'  => 'Your import permit application with id :id is saved as draft',
    'public_submit' => 'Your import permit application with id :id is submitted',

    // SMS messages (used in NotificationController – we'll keep them as keys too)
    'sms_importer_approval'   => 'An application needs your approval.',
    'sms_applicant_waiting'   => 'Your application has been successfully submitted and is waiting for approval.',
    'sms_applicant_success'   => 'Your application has been successfully submitted.',

    'status_clerk_review' => [
        'public'   => 'Your application has been verified by the importer and is now pending clerk review.',
        'internal' => 'Application verified by importer and awaiting clerk review.',
        'notify'   => 'Import application is now awaiting clerk review.',
    ],
    'status_not_approved' => [
        'public'   => 'Your application was not approved by the importer.',
        'internal' => 'Application was not approved by the importer.',
        'notify'   => 'Import application was not approved by the importer.',
    ],
    'status_clerk_verified' => [
        'public'   => 'Your application has been approved by the clerk.',
        'internal' => 'Application approved by clerk.',
        'notify'   => 'Import application has been approved by clerk.',
    ],
    'status_clerk_rejected' => [
        'public'   => 'Your application has been rejected by the clerk.',
        'internal' => 'Application rejected by clerk.',
        'notify'   => 'Import application has been rejected by clerk.',
    ],
];
