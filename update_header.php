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
        
        // Add data-admin-only to the Dashboard button in the header if it doesn't have it
        $content = preg_replace('/(<button class="btn-dashboard"[^>]*>.*?<\/button>)/i', function($matches) {
            $btn = $matches[1];
            if (strpos($btn, 'data-admin-only') === false) {
                return str_replace('<button class="btn-dashboard"', '<button class="btn-dashboard" data-admin-only', $btn);
            }
            return $matches[0];
        }, $content);
        
        file_put_contents($file, $content);
        echo "Updated header in $file\n";
    } else {
        echo "File not found: $file\n";
    }
}
?>
