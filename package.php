<?php

use nova\plugin\corn\Schedule;

return [
  "register" => Schedule::class,
  "require" => [
   "task"
  ],
];