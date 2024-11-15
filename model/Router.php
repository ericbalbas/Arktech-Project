<?php
namespace model;

use Exception;
use model\Automation;

class Router
{
    private $routes = [];
    
    public function add($routes, $action, $method = "GET")
    {
        $this->routes[$method][$routes] = $action;
    }

    public function HttpHandlerRequest()
    {
        // Determine request method (GET or POST)
        $method = $_SERVER['REQUEST_METHOD'];
        $route = isset($_GET['route']) ? $_GET['route'] : '';

        // Check if the route exists for the given request method (GET or POST)
        if (!isset($this->routes[$method][$route])) {
            throw new Exception("Route not found: $route with method $method");
        }

        // Get the action (the callable function or method)
        $action = $this->routes[$method][$route];

        // Check if the action is callable
        if (!is_callable($action)) {
            throw new Exception("Action for route $route is not callable.");
        }
        // print_r($_FILES);
        // Get the parameters based on the request method
        $params = ($method === 'POST') ? array_merge($_POST, $_FILES) : $_GET;

        print_r($params);
     
        unset($params['route']); // Remove 'route' param from the array

        $automation = new Automation();

        foreach ($params as $key => $value) {
            // Construct the setter method name based on the property name
            $setterMethod = 'set' . ucfirst($key);
            echo $setterMethod;
            if (method_exists($automation, $setterMethod)) {
                $automation->$setterMethod($value);
            }
        }

        // Display the automation object for debugging   
        // echo "<pre>" . print_r($automation, true) . "</pre>";

        call_user_func($action, $automation);
    }

    public function getRoutes()
    {
        echo "<pre>".print_r($this->routes, true)."</pre>";
    }

}

?>