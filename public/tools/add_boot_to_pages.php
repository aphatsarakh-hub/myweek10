<?php
// Usage: php tools/add_boot_to_pages.php
// This script will scan the project for .php files and insert a require for includes/boot.php

$root = realpath(__DIR__ . '/..');
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$count = 0;
$skip = [
    __FILE__,
    $root . '/includes/boot.php'
];

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getRealPath();
    if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') continue;
    if (in_array($path, $skip)) continue;

    $contents = file_get_contents($path);
    if (strpos($contents, "includes/boot.php") !== false) continue;

    // try to insert require after opening <?php or at top
    if (preg_match('/^<\?php\s*/', $contents)) {
        $new = preg_replace('/^(<\?php\s*)/','$1require_once __DIR__ . \'/../includes/boot.php\';\n', $contents, 1);
    } else {
        $new = "<?php require_once __DIR__ . '/includes/boot.php'; ?>\n" . $contents;
    }

    file_put_contents($path, $new);
    $count++;
}

echo "Updated $count files.\n";
