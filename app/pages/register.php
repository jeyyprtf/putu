<?php
if (!defined('PUTU_APP')) { header('Location: ../../index.php?page=register'); exit; }
$isRegister = true;
require __DIR__ . '/../components/auth-form.php';
