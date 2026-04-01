<?php
$files = [
    'resources/views/accounting/balance/pdf.blade.php',
    'resources/views/accounting/bilan/pdf.blade.php',
    'resources/views/accounting/journal/pdf.blade.php',
    'resources/views/accounting/journal/show-pdf.blade.php',
    'resources/views/accounting/ledger/pdf.blade.php',
    'resources/views/accounting/resultat/pdf.blade.php'
];

$fontLink = "    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n    <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap\" rel=\"stylesheet\">";

foreach ($files as $file) {
    if (!file_exists($file)) continue;

    $content = file_get_contents($file);

    // Inject Google font link if not present
    if (!str_contains($content, 'fonts.googleapis.com/css2?family=Inter')) {
        $content = preg_replace('/(<title>.*<\/title>)/i', "$1\n$fontLink", $content);
    }

    // Replace body font-family
    $content = preg_replace('/font-family:\s*sans-serif;/i', "font-family: 'Inter', sans-serif;", $content);

    // Make sure tailwind config includes the font
    $tailwindConfig = "fontFamily: { sans: ['Inter', 'sans-serif'], },\n                    colors:";
    if (!str_contains($content, 'fontFamily: { sans:')) {
        $content = str_replace('colors:', $tailwindConfig, $content);
    }

    file_put_contents($file, $content);
}
echo "Fonts updated for all PDFs.\n";
