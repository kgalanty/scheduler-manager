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
if (!($_SESSION['adminid'] && $_SESSION['adminpw'])) {
    //die("Not logged as admin");
}
define('ROUTE_PREFIX', '/schedule/scheduleapi');

$app->get(ROUTE_PREFIX . '/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});
$app->get(ROUTE_PREFIX . '/home', 'App\Controllers\HomeController:home');
$app->get(ROUTE_PREFIX . '/dbmigration', 'App\Controllers\MigrationController:home');
$app->get(ROUTE_PREFIX . '/agents', 'App\Controllers\AgentsController:agents');
$app->get(ROUTE_PREFIX . '/agents/myinfo', 'App\Controllers\AgentsController:myinfo');
$app->get(ROUTE_PREFIX . '/agents/{agentid}', 'App\Controllers\AgentsController:getAgentInfo');
$app->get(ROUTE_PREFIX . '/agents/{agentid}/timetable', 'App\Controllers\AgentsController:getTimetable');
$app->post(ROUTE_PREFIX . '/agents/editor', 'App\Controllers\AgentsController:changeEditorPermission')->add(new JsonMiddleware());
$app->get(ROUTE_PREFIX . '/shifts', 'App\Controllers\ShiftsController:getShifts');

$app->post(ROUTE_PREFIX . '/shift/new', 'App\Controllers\ShiftsController:insertShift')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/delete', 'App\Controllers\ShiftsController:deleteShift')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shift/showontopbar', 'App\Controllers\ShiftsController:showOnTopbar')->add(new JsonMiddleware());
$app->get(ROUTE_PREFIX . '/shift/showontopbar', 'App\Controllers\ShiftsController:getShowOnTopbar');

$app->post(ROUTE_PREFIX . '/groups/delete', 'App\Controllers\ShiftsController:deleteGroup')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/agents/addgroup', 'App\Controllers\AgentsController:addGroup')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/timetable', 'App\Controllers\TimetableController:insertDuty')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/delete_duty', 'App\Controllers\TimetableController:deleteDuty')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/agents/assigntogroup', 'App\Controllers\AgentsController:addMemberToTeam')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/agents/color', 'App\Controllers\AgentsController:setAgentsColor')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/delete_draft', 'App\Controllers\TimetableController:deleteDraft')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/commit', 'App\Controllers\TimetableController:commitDrafts')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/revert', 'App\Controllers\TimetableController:revertDrafts');
$app->post(ROUTE_PREFIX . '/editors/add', 'App\Controllers\EditorsController:add')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/editors/delete', 'App\Controllers\EditorsController:delete')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shifts/reorder/{direction}', 'App\Controllers\ShiftsController:reorder')->add(new JsonMiddleware());

$app->post(ROUTE_PREFIX . '/teams/reorder/{direction}', 'App\Controllers\SubteamsController:reorder')->add(new JsonMiddleware());
$app->get(ROUTE_PREFIX . '/teams', 'App\Controllers\TeamsController:get');

$app->post(ROUTE_PREFIX . '/templates/add', 'App\Controllers\TemplatesController:add')->add(new JsonMiddleware());
$app->get(ROUTE_PREFIX . '/templates/{groupid}', 'App\Controllers\TemplatesController:list');
$app->post(ROUTE_PREFIX . '/templates/confirm', 'App\Controllers\TemplatesController:confirm')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/templates/delete', 'App\Controllers\TemplatesController:delete')->add(new JsonMiddleware());

$app->get(ROUTE_PREFIX . '/stats', 'App\Controllers\StatsController:get');
$app->get(ROUTE_PREFIX . '/leaverequests', 'App\Controllers\LeaveRequests:requestslist');
$app->get(ROUTE_PREFIX . '/calendar/generate', 'App\Controllers\CalendarController:create');
$app->get(ROUTE_PREFIX . '/calendar/{agenthash}', 'App\Controllers\CalendarController:usercalendar');
$app->get(ROUTE_PREFIX . '/calendar', 'App\Controllers\CalendarController:accesslist');
$app->post(ROUTE_PREFIX . '/calendar/revoke', 'App\Controllers\CalendarController:revokeaccess')->add(new JsonMiddleware());

$app->post(ROUTE_PREFIX . '/subteams/color', 'App\Controllers\SubteamsController:setcolor')->add(new JsonMiddleware());

$app->get(ROUTE_PREFIX . '/editors/list', 'App\Controllers\EditorsController:list');
$app->get(ROUTE_PREFIX . '/shifts/teams', 'App\Controllers\AgentsController:teamsMembers');

$app->get(ROUTE_PREFIX . '/group/{groupid}/agents', 'App\Controllers\AgentsController:agentsTeams');
$app->get(ROUTE_PREFIX . '/groups/agents', 'App\Controllers\AgentsController:agentsTeams');

$app->get(ROUTE_PREFIX . '/daysoff/{agentid}', 'App\Controllers\DaysoffController:getDaysOff');

$app->post(ROUTE_PREFIX . '/daysoff/{agentid}', 'App\Controllers\DaysoffController:postDaysOff')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/daysoff/entry/{entryid}', 'App\Controllers\DaysoffController:changeDaysOff')->add(new JsonMiddleware());

$app->post(ROUTE_PREFIX . '/group/{groupid}/notifications/{date}', 'App\Controllers\NotifyController:notifyAgents');

$app->post(ROUTE_PREFIX . '/shift/{shiftid}/hide', 'App\Controllers\ShiftsController:hideShift')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/shift/{shiftid}/show', 'App\Controllers\ShiftsController:showShift')->add(new JsonMiddleware());

$app->get(ROUTE_PREFIX . '/group/{groupid}/drafts', 'App\Controllers\ShiftsController:loadDrafts');
$app->get(ROUTE_PREFIX . '/shifts/shiftsgroups/{groupid}', 'App\Controllers\ShiftsController:shiftsGroups');
$app->get(ROUTE_PREFIX . '/logs', 'App\Controllers\LogsController:get');
$app->get(ROUTE_PREFIX . '/verify', 'App\Controllers\AgentsController:verifyAgent');
$app->get(ROUTE_PREFIX . '/verifyadmin', 'App\Controllers\AgentsController:verifyAdmin');
$app->get(ROUTE_PREFIX . '/report/{datestart}/{dateend}', 'App\Controllers\TimetableController:scheduleForWorker');
$app->get(ROUTE_PREFIX . '/vacationing', 'App\Controllers\TimetableController:vacationing');
$app->post(ROUTE_PREFIX . '/vacationing', 'App\Controllers\TimetableController:vacationingStore')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/vacationing/delete', 'App\Controllers\TimetableController:deleteVacationing')->add(new JsonMiddleware());

$app->post(ROUTE_PREFIX . '/leave/submit', 'App\Controllers\LeaveRequestsController:submitrequest')->add(new JsonMiddleware());
$app->get(ROUTE_PREFIX . '/leave/get/{agentid}', 'App\Controllers\LeaveRequestsController:requestslist');
$app->get(ROUTE_PREFIX . '/leave/get', 'App\Controllers\LeaveRequestsController:requestslist');
$app->post(ROUTE_PREFIX . '/leave/review/{id}', 'App\Controllers\LeaveRequestsController:submitreview')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/leave/cancel/{id}', 'App\Controllers\LeaveRequestsController:cancelLeave')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/leave/edit/{id}', 'App\Controllers\LeaveRequestsController:editLeave')->add(new JsonMiddleware());
$app->post(ROUTE_PREFIX . '/feedback', 'App\Controllers\FeedbackController:submitrequest')->add(new JsonMiddleware());

$app->get(ROUTE_PREFIX . '/tickets/operators', 'App\Controllers\AgentsController:AgentsFromTickets');
$app->get(ROUTE_PREFIX . '/tickets/personalstats', 'App\Controllers\AgentsController:AgentsPersonalStatsTickets');

$app->get(ROUTE_PREFIX . '/permissions/agent/{agentid}', 'App\Controllers\PermissionsController:getPermissions');
$app->post(ROUTE_PREFIX . '/permissions/agent/{agentid}', 'App\Controllers\PermissionsController:setPermissions')->add(new JsonMiddleware());
// $app->post(ROUTE_PREFIX.'/assignshift','App\Controllers\ShiftsController:assignToShift');
//$app->add($beforeMiddleware);
$app->run();