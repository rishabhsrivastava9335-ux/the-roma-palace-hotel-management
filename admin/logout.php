<?php
/**
 * THE ROMA PALACE — Admin Sign Out
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/../includes/auth.php';

logout_user();
session_start();
$_SESSION['flash_success'] = 'You have been safely signed out from the administration portal.';
header("Location: admin-login.php");
exit;
