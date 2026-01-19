<?php

use Illuminate\Support\Str;

return [

    /** App Setting */

    'authorize-login' => 'Iniciar sesión autorizado',
    'override-bssid' => 'Anular bssid',
    '24-hour-format' => 'Formato de 24 horas',
    'bs' => 'Fecha en BS',
    'attendance-note' => 'Nota de asistencia',

    /** General Setting */

    'firebase_key' => 'Clave de Firebase',
    'firebase_key_description' => 'La clave de Firebase es necesaria para enviar notificaciones al móvil.',
    'attendance_notify' => 'Establecer número de días para notificaciones push locales',
    'attendance_notify_description' => 'Configurar el número de días enviará automáticamente los datos de esos días a la aplicación móvil. Recibir estos datos en el móvil permitirá a la aplicación móvil configurar notificaciones push locales para esas fechas. Las notificaciones push locales ayudarán a los empleados a recordar que deben registrarse a tiempo y también a registrarse cuando el turno esté a punto de terminar.',
    'advance_salary_limit' => 'Límite de salario anticipado (%)',
    'advance_salary_limit_description' => 'Establezca el monto máximo en porcentaje que un empleado puede solicitar por adelantado basado en el salario bruto.',
    'employee_code_prefix' => 'Prefijo de código de empleado',
    'employee_code_prefix_description' => 'Este prefijo se usará para hacer el código de empleado.',
    'attendance_limit' => 'Límite de asistencia',
    'attendance_limit_description' => 'Límite de asistencia para el registro de entrada y salida.',
    'award_display_limit' => 'Límite de visualización de premios',
    'award_display_limit_description' => 'Límite de visualización de premios en la aplicación móvil.',
    'theme_color'=>'Color del tema',
    'records_per_page'=>'Registros por página',
    'records_per_page_description'=>'Mostrar el número de registros en la página de lista.',
];
