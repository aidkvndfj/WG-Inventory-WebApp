<?php

/**
 * extractQueryParams
 *
 * @return array
 */
function extractQueryParams()
{
    $query_params = [];
    $request_uri = $_SERVER['REQUEST_URI'];
    if (strpos($request_uri, '?') !== false) {
        list($path, $query_string) = explode('?', $request_uri, 2);
        parse_str($query_string, $query_params);
    }
    return $query_params;
}

/**
 * handleQueryParams
 *
 * @param  mixed $sql
 * @param  mixed $params
 * @param  mixed $db
 * @return array
 */
function handleQueryParams($sql, $params, $db)
{
    try {
        $conditions = [];
        $bindValues = [];

        $flags = [
            "_contains" => function ($paramKey, $paramValue) use (&$conditions, &$bindValues) {
                $conditions[] = "$paramKey LIKE ?";
                $bindValues[] = [getParamType($paramKey), '%' . trim($paramValue, "[]") . '%'];
            },
        ];

        foreach ($params as $paramKey => $paramValue) {
            // Check if the parameter value is enclosed in square brackets
            if (preg_match('/^\[.*\]$/', $paramValue)) {
                $paramValue = trim($paramValue, "[]");

                // Handle multiple values in brackets
                if (strpos($paramValue, ',') !== false) {
                    $values = explode(',', $paramValue);
                    $placeholders = [];
                    foreach ($values as $value) {
                        $hasFlag = false;
                        foreach ($flags as $flag => $action) {
                            if (strpos($value, $flag) !== false) {
                                $cleanParamValue = str_replace($flag, '', trim($value));
                                $action($paramKey, $cleanParamValue);
                                $hasFlag = true;
                                break;
                            }
                        }
                        // If no flag is found, use exact match
                        if (!$hasFlag) {
                            $placeholders[] = "$paramKey = ?";
                            $bindValues[] = [getParamType($paramKey), $value];
                        }
                    }
                    if (!empty($placeholders)) {
                        // Combine all conditions
                        $conditions[] = '(' . implode(" AND ", $placeholders) . ')';
                    }
                } else {
                    handleParam($paramKey, $paramValue, $flags, $conditions, $bindValues);
                }
            } else {
                handleParam($paramKey, $paramValue, $flags, $conditions, $bindValues);
            }
        }

        $data = [];

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $stmt = $db->getConnection()->prepare($sql);

        if (!empty($bindValues)) {
            $types = implode('', array_column($bindValues, 0));
            $bindParameters = array_column($bindValues, 1);

            $paramCount = count($bindParameters);
            $params = array_merge([$types], $bindParameters);

            $stmt->bind_param(...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();


        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    } catch (Exception $e) {
        return $e;
    }
}

/**
 * handleParam
 *
 * @param  mixed $paramKey
 * @param  mixed $paramValue
 * @param  mixed $flags
 * @param  mixed $conditions
 * @param  mixed $bindValues
 * @return void
 */
function handleParam($paramKey, $paramValue, $flags, &$conditions, &$bindValues)
{
    $hasFlag = false;
    foreach ($flags as $flag => $action) {
        if (strpos($paramValue, $flag) !== false) {
            $cleanParamValue = str_replace($flag, '', trim($paramValue));
            $action($paramKey, $cleanParamValue);
            $hasFlag = true;
            break;
        }
    }
    if (!$hasFlag) {
        $conditions[] = "$paramKey = ?";
        $bindValues[] = [getParamType($paramKey), $paramValue];
    }
}

/**
 * getParamType
 *
 * @param  mixed $paramKey
 * @return string
 */
function getParamType($paramKey)
{
    $paramTypes = [
        'code' => 's',
        'credits' => 's',
        'prerequisites' => 's',
    ];
    return $paramTypes[$paramKey] ?? 's';
}

/**
 * handleError
 *
 * @param  mixed $code
 * @param  mixed $status
 * @param  mixed $info
 * @return json_string
 */
function handleError($code, $status, $info = "")
{
    $errorMessages = [
        INVALID_INPUT_ERROR => "Invalid input provided.",
        MISSING_PARAMS_ERROR => "Please provide all required parameters.",
        DUPLICATE_COURSE_ERROR => "A course with the same code already exists.",
        INTERNAL_SERVER_ERROR => "An error has occured.",
        COURSE_CREATE_ERROR => "An error has occured, course not created.",
        COURSE_UPDATE_ERROR => "An error has occured, course not updated.",
        COURSE_NOT_FOUND => "No course found with the provided course id.",
        COURSE_UPDATE_NO_VALUES => "Please provide at least 1 value to update.",
        ENDPOINT_NOT_FOUND_ERROR => "Endpoint not found.",
        ROUTE_METHOD_NOT_ALLOWED => "Method not allowed for this route.",
        COURSE_CODE_NOT_PROVIDED => "Course code not provided.",
        DB_CONNECTION_FAILED => "Connection to database not established.",
        NO_AUTH_TOKEN_FOUND => "No auth token."
    ];

    http_response_code($status);

    if (array_key_exists($code, $errorMessages)) {
        return json_encode([
            'http_response_code' => $status,
            'error_code' => $code,
            'error_message' => $errorMessages[$code],
            'info' => $info,
        ]);
    } else {
        // Generic case
        return json_encode([
            'http_response_code' => $status,
            'error_code' => $code,
            'error_message' => "An error occurred.",
            'info' => $info,
        ]);
    }
}

/**
 * REQ_BODY
 *
 * @return array
 */
function REQ_BODY()
{
    return json_decode(file_get_contents('php://input'), true);
}

/**
 * deployToProd
 *
 * @return void
 */
function deployToProd()
{
    $body = REQ_BODY();
    $token = $body['authToken'];
    $sprint = $body['sprint'];

    if ($token != DEPLOY_AUTH_TOKEN) return;

    putenv('PATH=' . getenv('PATH') . ':/usr/bin');
    $switchCommand = 'sudo -u socs git switch ' . $sprint;
    $outputSwitch = shell_exec($switchCommand);
    $outputPull = shell_exec('sudo -u socs git pull 2>&1');

    echo "<pre>$outputSwitch</pre>";
    echo "<pre>$outputPull</pre>";
}
