<?php

require_once __DIR__.'/../controllers/admin/DeleteStudent.php';

$jwt_token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;

