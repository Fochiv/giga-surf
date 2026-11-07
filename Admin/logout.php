<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('Admin/login.php');
}
verifyCsrf();
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);
flash('success', 'Vous êtes déconnecté.');
redirect('Admin/login.php');
