<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
//use WHMCS\Database\Capsule as DB;
//require __DIR__ . '/../../init.php';
require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
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
// $app->get('/', function (Request $request, Response $response, $args) {
//     $response->getBody()->write("Hello world!");
//     return $response;
// });
$app->get('/home','App\Controllers\HomeController:home');
$app->run();