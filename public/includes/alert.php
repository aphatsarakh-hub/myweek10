<?php
function renderFlashAlert() {
    if (!empty($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        echo '<div style="padding:10px;background:#e0f7fa;border:1px solid #b2ebf2;margin:16px 0;">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
