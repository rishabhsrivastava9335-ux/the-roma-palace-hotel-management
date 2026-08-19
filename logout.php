<?php
/**
 * THE ROMA PALACE — Sign Out Handler
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/auth.php';

logout_user();
session_start();
$_SESSION['flash_success'] = 'You have been safely signed out. We look forward to welcoming you again.';
header("Location: login.php");
exit;
