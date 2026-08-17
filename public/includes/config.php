<?php
// กำหนดค่าพื้นฐานสำหรับโปรเจกต์
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}
if (!defined('BASE_URL')) {
    $projectRoot = realpath(__DIR__ . '/..');
    $documentRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : null;
    $baseUrl = '/BamBam_Cat_Hotel';

    if ($documentRoot && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
        $baseUrl = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
        if ($baseUrl === '' || $baseUrl === false) {
            $baseUrl = '/';
        }
    }

    if ($baseUrl !== '/' && substr($baseUrl, 0, 1) !== '/') {
        $baseUrl = '/' . $baseUrl;
    }
    $baseUrl = rtrim($baseUrl, '/');
    define('BASE_URL', $baseUrl === '' ? '/' : $baseUrl);
}

date_default_timezone_set('Asia/Bangkok');
