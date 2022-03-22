<?php

namespace App\Functions;

class TimetableHelper
{
    /*
    *
        object(stdClass)#310 (15) {
  ["id"]=>
  int(629)
  ["shift_id"]=>
  int(28)
  ["day"]=>
  string(10) "2022-02-20"
  ["firstname"]=>
  NULL
  ["lastname"]=>
  NULL
  ["color"]=>
  NULL
  ["bg"]=>
  NULL
  ["draft"]=>
  int(0)
  ["author"]=>
  int(230)
  ["draftauthor"]=>
  NULL
  ["from"]=>
  string(5) "07:00"
  ["to"]=>
  string(5) "15:00"
  ["agcolor"]=>
  NULL
  ["agbgcolor"]=>
  NULL
  ["agent_id"]=>
  int(0)
}
    */
    public static function renderPlaceholder(\StdClass $t)
    {
        return [
            'id' => $t->id,
            'agent' => 'I NEED HELP',
            'color' => 'yellow',
            'bg' => 'red',
            'author' => $t->author,
            'draft' => $t->draft,
            'deldraftauthor' => $t->draftauthor ?? false,
            'shift' => $t->from . '-' . $t->to,
            'date' => $t->day
        ];
    }
    public static function renderNormalShift(\StdClass $t)
    {
        return  [
            'id' => $t->id,
            'agent' => $t->firstname . ' ' . $t->lastname,
            'color' => $t->agcolor != 'null' ?  $t->agcolor : '#000',
            'bg' => $t->agbgcolor!= 'null'  ?  $t->agbgcolor : 'rgb(202 202 202)',
            'author' => $t->author,
            'draft' => $t->draft,
            'deldraftauthor' => $t->draftauthor ?? false,
            'shift' => $t->from . '-' . $t->to,
            'date' => $t->day
        ];
    }
    public static function renderTimetableRecord(\StdClass $t)
    {
        if($t->agent_id == -1)
        {
            return self::renderPlaceholder($t);
        }
        else
        {
            return self::renderNormalShift($t);
        }
    }
}