<?php

return [
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
];
