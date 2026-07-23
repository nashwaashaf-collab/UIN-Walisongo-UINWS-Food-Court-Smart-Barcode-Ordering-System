<?php
require_once __DIR__ . '/helper/functions.php';
session_destroy();
redirect('index.php');
