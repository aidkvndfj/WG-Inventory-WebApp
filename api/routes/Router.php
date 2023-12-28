<?php
define('DB_HOST_1', '192.168.2.148
');
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
if ($host == DB_HOST_1) {
    include 'api/constants/index.php';
    include 'api/utils/index.php';
} else {
    include 'constants/index.php';
    include 'utils/index.php';
}

class Router
{
    private $routes = [];

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        // Define all of our routes and their corresponding controllers
        $this->routes = [
            API_BASE_URI . COURSES_URI => ['controller' => COURSE_CONTROLLER, 'methods' => [GET, POST]],
        ];
    }

    /**
     * dispatchRequest
     * Redirects requests to their coresponding controller
     * @return void
     */
    public function dispatchRequest()
    {

        try {
            $uri = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];

            if (strpos($uri, '/deploy') && $method == POST) {
                deployToProd();
                exit;
            }

            $uriWithoutQuery = strtok($uri, '?');

            // Iterate through defined routes to find a match
            foreach ($this->routes as $route => $routeData) {
                if (preg_match('~^' . $route . '$~', $uriWithoutQuery, $matches)) {
                    $allowedMethods = isset($routeData['methods']) ? $routeData['methods'] : [GET];

                    if (in_array($method, $allowedMethods)) {
                        $controllerName = $routeData['controller'];

                        if ($_SERVER['HTTP_HOST'] == DB_HOST_1) {
                            include_once "api/controllers/$controllerName.php";
                        } else {
                            include_once "controllers/$controllerName.php";
                        }

                        $controller = new $controllerName();

                        // Extract query parameters
                        $queryParams = extractQueryParams();

                        // Determine the action based on the HTTP method
                        switch ($method) {
                            case GET:
                                if (strpos($matches[0], 'allsubjects')) {
                                    $controller->getSubjects($queryParams);
                                } else if (strpos($matches[0], 'courseinsubject') && isset($matches[1])) {
                                    $controller->getSubjectCodes($matches[1], $queryParams);
                                } else if (isset($matches[1])) {
                                    $controller->getByCode($matches[1], $queryParams);
                                } else {
                                    $controller->get($queryParams);
                                }
                                break;

                            case POST:
                                $controller->create();
                                break;

                            case PUT:
                                if (isset($matches[1])) {
                                    $controller->update($matches[1]);
                                } else {
                                    throw new Exception(handleError(ENDPOINT_NOT_FOUND_ERROR, 400, 'Course code not provided for update'));
                                }
                                break;

                            case DELETE:
                                if (isset($matches[1])) {
                                    $controller->delete($matches[1]);
                                } else {
                                    throw new Exception(handleError(ENDPOINT_NOT_FOUND_ERROR, 400, 'Course code not provided for deletion'));
                                }
                                break;

                            default:
                                throw new Exception(handleError(ENDPOINT_NOT_FOUND_ERROR, 405));
                                break;
                        }
                    } else {

                        throw new Exception(handleError(ENDPOINT_NOT_FOUND_ERROR, 405));
                    }
                    exit;
                }
            }
            throw new Exception(handleError(ENDPOINT_NOT_FOUND_ERROR, 404));
        } catch (Exception $e) {
            echo $e;
        }
    }
}
