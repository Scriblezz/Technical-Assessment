<?php

return [
    'secret' => env('JWT_SECRET', 'dev-secret-change-me'),
    'ttl' => env('JWT_TTL', 60),
    'algo' => env('JWT_ALGO', 'HS256'),
    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
];
