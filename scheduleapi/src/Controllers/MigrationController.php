<?php

namespace App\Controllers;

use WHMCS\Database\Capsule as DB;
use App\Constants\AgentConstants;
use App\Responses\Response;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrationController
{
  public function home($request, $response, $args)
  {
    $schemaBuilder = \WHMCS\Database\Capsule::schema();
    if (!$schemaBuilder->hasTable('schedule_agents_details')) {
      $schemaBuilder->create('schedule_agents_details', function ($table) {
          $table->increments("id");
          $table->integer("agent_id");
          $table->string("color")->default(NULL);
          $table->string("bg")->default(NULL);
      });
    }
    if (!$schemaBuilder->hasTable('schedule_agents_shifts')) {
      $schemaBuilder->create('schedule_agents_shifts', function ($table) {
          $table->increments("id");
          $table->string('agent_id', 64);
          $table->string('shift_id', 64);
      });
    }
    if (!$schemaBuilder->hasTable('schedule_agents_to_groups')) {
      $schemaBuilder->create('schedule_agents_to_groups', function ($table) {
          $table->increments("id");
          $table->integer('group_id');
          $table->integer('agent_id');
      });
    }
    if (!$schemaBuilder->hasTable('schedule_agentsgroups')) {
      $schemaBuilder->create('schedule_agentsgroups', function ($table) {
          $table->increments("id");
          $table->string('group');
      });
    }
    if (!$schemaBuilder->hasTable('schedule_editors')) {
      $schemaBuilder->create('schedule_editors', function ($table) {
          $table->increments("id");
          $table->integer('editor_id', false, true);
      });
    }
    if (!$schemaBuilder->hasTable('schedule_eventslog')) {
      $schemaBuilder->create('schedule_eventslog', function ($table) {
          $table->increments("id");
          $table->integer('author', false, true)->default('0');
          $table->text('log');
          $table->date('event_date');
          $table->string('action', 255)->default(NULL);
          $table->string('path', 255)->default(NULL);
          $table->dateTime('date');
      });
    }
    if (!$schemaBuilder->hasTable('schedule_shifts')) {
      $schemaBuilder->create('schedule_shifts', function ($table) {
          $table->increments("id");
          $table->integer('group_id');
          $table->string('from', 64);
          $table->string('to', 64);
      });
    }
    if (!$schemaBuilder->hasTable('schedule_templates')) {
      $schemaBuilder->create('schedule_templates', function ($table) {
          $table->increments("id");
          $table->string('name', 255);
          $table->integer('group_id', false,true);

      });
    }
    if (!$schemaBuilder->hasTable('schedule_timetable')) {
      $schemaBuilder->create('schedule_timetable', function ($table) {
          $table->increments("id");
          $table->string('agent_id', 64);
          $table->integer('group_id');
          $table->string('shift_id', 64);
          $table->date('day');
          $table->tinyInteger('draft')->default(NULL);
          $table->integer('author')->default('0');
      });
    }
    if (!$schemaBuilder->hasTable('schedule_timetable_deldrafts')) {
      $schemaBuilder->create('schedule_timetable_deldrafts', function ($table) {
          $table->increments("id");
          $table->integer('entry_id');
          $table->integer('author')->default('0');
          $table->index(['entry_id']);
      });
    }
    if (!$schemaBuilder->hasTable('schedule_tplshifts')) {
      $schemaBuilder->create('schedule_tplshifts', function ($table) {
          $table->increments("id");
          $table->integer('tpl_id');
          $table->char('day', 1);
          $table->integer('agent_id');
          $table->integer('shift_id');
      });
    }
    if (!$schemaBuilder->hasTable('schedule_vacations')) {
      $schemaBuilder->create('schedule_vacations', function ($table) {
          $table->increments("id");
          $table->string('agent_id', 64);
          $table->integer('group_id');
          $table->date('day');
          $table->tinyInteger('draft', false, true)->default(NULL);
          $table->integer('author')->default('0');
          $table->index('day');
          $table->index('group_id');
      });
    }
    // $id = $request->getParsedBody()['id'];
    // //$author = $_SESSION['adminpw'];
    // $author = AgentConstants::adminid();
    // DB::table("schedule_timetable_deldrafts")->where('entry_id', $id)->where('author', $author)->delete();
    $data['response'] = 'success';
    return Response::json($data, $response);
    // $payload = json_encode($data);
    // $response->getBody()->write($payload);
    // return $response
    //   ->withHeader('Content-Type', 'application/json');
  }
}
