<?php
// General
define('API_BASE_URI', '/api/v1/');
define('COURSES_URI', 'courses');
define('COURSE_CONTROLLER', 'CourseController');
define('GET', 'GET');
define('POST', 'POST');
define('PUT', 'PUT');
define('PATCH', 'PATCH');
define('DELETE', 'DELETE');
define('CODE', 'code');
define('CREDITS', 'credits');
define('PREREQUISITES', 'prerequisites');

// Local Database
define('DB_SERVER_NAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'TBD');

// Error codes
define('INVALID_INPUT_ERROR', 101);
define('DUPLICATE_COURSE_ERROR', 102);
define('INTERNAL_SERVER_ERROR', 103);
define('COURSE_CREATE_ERROR', 104);
define('MISSING_PARAMS_ERROR', 105);
define('COURSE_NOT_FOUND', 106);
define('COURSE_UPDATE_ERROR', 107);
define('COURSE_UPDATE_NO_VALUES', 108);
define('ENDPOINT_NOT_FOUND_ERROR', 109);
define('ROUTE_METHOD_NOT_ALLOWED', 110);
define('COURSE_CODE_NOT_PROVIDED', 111);
define('DB_CONNECTION_FAILED', 112);
define('NO_AUTH_TOKEN_FOUND', 113);
