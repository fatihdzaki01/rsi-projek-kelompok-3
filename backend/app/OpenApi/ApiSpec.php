<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Berbagive API',
    version: '1.0.0',
    description: 'API untuk Sistem Informasi Penyaluran dan Pengelolaan Bantuan Sosial Berbasis Komunitas',
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
)]
class ApiSpec
{
}
