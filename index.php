<?php
require_once 'pms.php';
require_once 'autoload.php';

use model\Automation;
use repo\AutomationRepository;
use config\HTMLBuilder;
use controller\AutomationController;
use model\Route;
use model\Router; 
use server\Database;

$cpt = new HTMLBuilder; 
$controller = new AutomationController;;
// $database = new Database();
// $repository = new AutomationRepository($database);

// header manipulation
$tpl = new PMSTemplates; // Declare Once
$displayId = "18"; // GROUP WORK SCHEDULE
$header = new ResponsiveHeader($displayId);
$title = displayText($displayId, 'utf8', 0, 1, 1);
getTableDesign();

$routes = $_GET['route']? $_GET['route'] : null;

if($routes)
{
    $controller->requestHandler();
}

require_once 'view/automations.php';

?>