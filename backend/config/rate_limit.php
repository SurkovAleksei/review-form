<?php

return [
    'requests' => env('RATE_LIMIT_REQUESTS', 5),
    'window' => env('RATE_LIMIT_WINDOW', 3600),
];
