<?php

use Illuminate\Support\Str;

return [

    /** App Setting */
    'authorize-login' => 'Login autorizado',
    'override-bssid' => 'Substituir bssid',
    '24-hour-format' => 'Formato de 24 horas',
    'bs' => 'Data em BS',
    'attendance-note' => 'Nota de presença',


    /** General Setting */

    'firebase_key' => 'Chave Firebase',
    'firebase_key_description' => 'A chave Firebase é necessária para enviar notificações para o celular.',
    'attendance_notify' => 'Definir número de dias para notificações push locais',
    'attendance_notify_description' => 'Definir o número de dias enviará automaticamente os dados desses dias para o aplicativo móvel. Receber esses dados no celular permitirá que o aplicativo móvel configure notificações push locais para essas datas. As notificações push locais ajudarão os funcionários a lembrar-se de fazer check-in a tempo e também a fazer check-out quando o turno estiver prestes a terminar.',
    'advance_salary_limit' => 'Limite de Salário Adiantado (%)',
    'advance_salary_limit_description' => 'Defina o valor máximo em porcentagem que um funcionário pode solicitar antecipadamente com base no salário bruto.',
    'employee_code_prefix' => 'Prefixo do Código do Funcionário',
    'employee_code_prefix_description' => 'Este prefixo será usado para criar o código do funcionário.',
    'attendance_limit' => 'Limite de Presença',
    'attendance_limit_description' => 'Limite de presença para check-in e check-out.',
    'award_display_limit' => 'Limite de Exibição de Prêmios',
    'award_display_limit_description' => 'Limite de exibição de prêmios no aplicativo móvel.',
    'theme_color'=>'Cor do tema',
    'records_per_page'=>'Registros por página',
    'records_per_page_description'=>'Exibir o número de registros na página de lista.',

];
