<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Middleware\JsonMiddleware;

require_once __DIR__ . '/../../init.php';
require './vendor/autoload.php';
header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Origin: localhost');
$app = AppFactory::create();
// $app->addRoutingMiddleware();
// $errorMiddleware = $app->addErrorMiddleware(true, true, true);
if(!($_SESSION['adminid'] && $_SESSION['adminpw']))
{
   // die('Unauthorized');
}
define('ROUTE_PREFIX', '/710/schedule/scheduleapi');


$app->get(ROUTE_PREFIX.'/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $data = ['test' => $name];
    $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
});
$app->get('/agents', function ($request, $response, $args) {
    $data = [];
    
    $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
});
$app->get('/shifts[/{id}]', function ($request, $response, $args) {
    $data = [];
    // Show book identified by $args['id']
    // ...
    
    $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
});
$app->get(ROUTE_PREFIX.'/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});
$app->get(ROUTE_PREFIX.'/home','App\Controllers\HomeController:home');

$app->get(ROUTE_PREFIX.'/agents','App\Controllers\AgentsController:agents');
$app->get(ROUTE_PREFIX.'/agents/myinfo','App\Controllers\AgentsController:myinfo');
$app->get(ROUTE_PREFIX.'/shifts','App\Controllers\ShiftsController:getShifts');

$app->post(ROUTE_PREFIX.'/insertshift','App\Controllers\ShiftsController:insertShift')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/shifts/delete','App\Controllers\ShiftsController:deleteShift')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/groups/delete','App\Controllers\ShiftsController:deleteGroup')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/agents/addgroup','App\Controllers\AgentsController:addGroup')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/shifts/timetable','App\Controllers\TimetableController:insertDuty')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/shifts/delete_duty','App\Controllers\TimetableController:deleteDuty')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/agents/assigntogroup','App\Controllers\AgentsController:addMemberToTeam')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/agents/color','App\Controllers\AgentsController:setAgentsColor')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/shifts/delete_draft','App\Controllers\TimetableController:deleteDraft')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX.'/shifts/commit','App\Controllers\TimetableController:commitDrafts');
$app->post(ROUTE_PREFIX.'/shifts/revert','App\Controllers\TimetableController:revertDrafts');

$app->get(ROUTE_PREFIX.'/shifts/teams','App\Controllers\AgentsController:teamsMembers');
$app->get(ROUTE_PREFIX.'/shifts/shiftsgroups/{groupid}','App\Controllers\ShiftsController:shiftsGroups');
$app->get(ROUTE_PREFIX.'/logs','App\Controllers\LogsController:get');
// $app->post(ROUTE_PREFIX.'/assignshift','App\Controllers\ShiftsController:assignToShift');
//$app->add($beforeMiddleware);
$app->run();