<?php

$dirs = ['resources/views'];

function processDir($dir) {
    if (!is_dir($dir)) return;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $path = $file->getPathname();
            $content = file_get_contents($path);
            
            // Replace gray with slate for Dougs theme
            $content = preg_replace('/\bgray-(50|100|200|300|400|500|600|700|800|900)\b/', 'slate-$1', $content);
            
            // Blue to primary
            $content = preg_replace('/\bblue-(400|500|600|700)\b/', 'primary', $content);
            $content = preg_replace('/\bblue-(100|200)\b/', 'slate-300', $content);

            // Also update any inline colors in PDF views
            if (str_contains($path, 'pdf.blade.php')) {
                // Change PDF table generic background colors 
                $content = str_replace('#f3f4f6', '#f8fafc', $content); // slate-50
                $content = str_replace('#f0f0f0', '#f8fafc', $content);
                $content = str_replace('#ddd', '#e2e8f0', $content); // slate-200
                $content = str_replace('#777', '#94a3b8', $content); // slate-400
                $content = str_replace('#333', '#0f172a', $content); // slate-900
            }

            file_put_contents($path, $content);
        }
    }
}

foreach ($dirs as $dir) {
    processDir(__DIR__ . '/../' . $dir);
}
echo "Colors updated successfully.\n";
