<?php
$files = [
    'Dashboard.html',
    'Employee Management-1.html',
    'Leave_History.html',
    'My_Profile.html',
    'Profile.html',
    'Recruitment.html',
    'Reports.html',
    'Training.html'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);

        // 1. Restore the My Profile, Personal Data Sheet, and Leave History if they are missing
        // It's safer to just inject them after Dashboard if they are missing.
        $hasMyProfile = strpos($content, 'My_Profile.html') !== false;
        if (!$hasMyProfile) {
            $insert = "\n        <li data-employee-only><a href=\"My_Profile.html\"><span class=\"nav-icon\">👤</span> My Profile</a></li>";
            $insert .= "\n        <li data-employee-only><a href=\"Profile.html\"><span class=\"nav-icon\">📋</span> Personal Data Sheet</a></li>";
            $insert .= "\n        <li data-employee-only><a href=\"Leave_History.html\"><span class=\"nav-icon\">📅</span> Leave History</a></li>";
            
            // Insert it after the Dashboard li
            $content = preg_replace('/(<li[^>]*><a href="Dashboard\.html".*?<\/li>)/i', '$1' . $insert, $content);
        }

        // 2. Make sure the Dashboard item in the *sidebar* has data-admin-only.
        // Look specifically for the <li> with Dashboard.html inside a <ul> inside the sidebar
        $content = preg_replace_callback('/<li([^>]*)>(<a href="Dashboard\.html".*?<\/a>)<\/li>/i', function($matches) {
            $liAttrs = $matches[1];
            $aTag = $matches[2];
            
            // If it's already got data-admin-only, leave it alone
            if (strpos($liAttrs, 'data-admin-only') !== false) {
                return $matches[0];
            }
            
            // Otherwise, inject data-admin-only
            return '<li' . $liAttrs . ' data-admin-only>' . $aTag . '</li>';
        }, $content);
        
        // Let's also ensure the top header Dashboard button is data-admin-only too,
        // since the user only wants employees to NOT see the dashboard at all.
        // Oh wait, user specifically said "dashboard in employee portal sides right",
        // this implies the sidebar and header button.
        
        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}
?>
