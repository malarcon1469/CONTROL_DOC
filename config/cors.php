<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | Puedes definir rutas específicas o patrones que deberían tener
    | cabeceras CORS aplicadas. Un asterisco '*' aplicará CORS a todas las rutas.
    | Por lo general, para una API, querrás 'api/*' o rutas específicas.
    | 'sanctum/csrf-cookie' es importante si usas la autenticación SPA de Sanctum.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Especifica qué métodos HTTP están permitidos. Un asterisco '*' permitirá
    | todos los métodos. Puedes ser específico como ['GET', 'POST', 'PUT', ...].
    |
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Define qué orígenes (dominios) están permitidos para hacer solicitudes
    | a tu API. Un asterisco '*' permitirá cualquier origen (no recomendado
    | para producción). Lista tus dominios de frontend aquí.
    | Ejemplo: ['http://localhost:3000', 'https://mi-spa.com']
    |
    */

    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'], // AJUSTA ESTO A TU NECESIDAD
    // 'allowed_origins' => ['*'], // Solo para desarrollo si es estrictamente necesario

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Puedes usar expresiones regulares para definir patrones de orígenes
    | permitidos. Por ejemplo, para permitir todos los subdominios de un dominio.
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Especifica qué cabeceras HTTP están permitidas en las solicitudes.
    | Un asterisco '*' permitirá todas las cabeceras. Es común necesitar
    | 'Content-Type', 'X-Requested-With', 'Authorization'.
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Define qué cabeceras de respuesta pueden ser accedidas por el navegador
    | del cliente. Por defecto, solo unas pocas cabeceras seguras son expuestas.
    | Si tu API devuelve cabeceras personalizadas que el frontend necesita leer,
    | añádelas aquí.
    |
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Define el valor de la cabecera `Access-Control-Max-Age`. Este valor
    | indica por cuánto tiempo (en segundos) el resultado de una solicitud
    | preflight (OPTIONS) puede ser cacheado por el cliente. Si es 0,
    | no se cacheará.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Si se establece en `true`, la cabecera `Access-Control-Allow-Credentials`
    | se enviará. Esto es necesario si tu frontend necesita enviar cookies
    | (como las de sesión de Sanctum SPA) o cabeceras de autenticación
    | con las solicitudes CORS.
    |
    */

    'supports_credentials' => true,

];