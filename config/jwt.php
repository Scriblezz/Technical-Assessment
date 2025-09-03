<?php

return [
    'secret' => env('JWT_SECRET', 'your-secret-key'),
    'ttl' => 60, // token time-to-live in minutes
    'algo' => 'HS256',
    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
];
