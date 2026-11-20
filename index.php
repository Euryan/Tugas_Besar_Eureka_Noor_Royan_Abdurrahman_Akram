<?php
require_once __DIR__ . '/auth.php';

$currentUser = current_user();
if ($currentUser !== null) {
    redirect_for_current_role();
}

header('Location: login.php');
exit;
