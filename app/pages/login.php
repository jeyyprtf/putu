<?php
if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=login'); exit; }
$isRegister = false;
require __DIR__ . '/../components/auth-form.php';
