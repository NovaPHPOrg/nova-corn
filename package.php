<?php

use nova\plugin\corn\Schedule;

return [
  "require" => [
   "task"
  ],
    "config"=>[
        "framework.start"=>[
            Schedule::class
        ]
    ]
];