<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Students Information API',
    description: 'Dokumentasi API Sistem Informasi Mahasiswa menggunakan Laravel & JWT Authentication',
    contact: new OA\Contact(name: 'API Support', email: 'support@example.com')
)]
#[OA\Server(
    url: '/api',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'bearerAuth',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Masukkan token JWT dengan format: Bearer <token>'
)]
abstract class Controller
{
    //
}
