<?php

use Illuminate\Support\Str;

return [

    /** App Setting */

    'authorize-login' => 'Autorisierte Anmeldung',
    'override-bssid' => 'BSSID überschreiben',
    '24-hour-format' => '24-Stunden-Format',
    'bs' => 'Datum im BS',
    'attendance-note' => 'Anwesenheitsnotiz',

    /** General Setting */

    'firebase_key' => 'Firebase-Schlüssel',
    'firebase_key_description' => 'Firebase-Schlüssel wird benötigt, um Benachrichtigungen an das Handy zu senden.',
    'attendance_notify' => 'Anzahl der Tage für lokale Push-Benachrichtigungen festlegen',
    'attendance_notify_description' => 'Das Festlegen der Anzahl der Tage sendet automatisch die Daten dieser Tage an die mobile Anwendung. Das Empfangen dieser Daten auf der mobilen Seite ermöglicht es der mobilen Anwendung, lokale Push-Benachrichtigungen für diese Daten einzurichten. Die lokalen Push-Benachrichtigungen helfen den Mitarbeitern, sich daran zu erinnern, sich rechtzeitig einzuchecken und auszuchecken, wenn die Schicht zu Ende geht.',
    'advance_salary_limit' => 'Vorschuss-Grenze (%)',
    'advance_salary_limit_description' => 'Legen Sie den maximalen Prozentsatz des Vorschusses fest, den ein Mitarbeiter auf Grundlage des Bruttogehalts beantragen kann.',
    'employee_code_prefix' => 'Mitarbeitercode-Präfix',
    'employee_code_prefix_description' => 'Dieses Präfix wird zur Erstellung des Mitarbeitercodes verwendet.',
    'attendance_limit' => 'Anwesenheitsgrenze',
    'attendance_limit_description' => 'Anwesenheitsgrenze für Ein- und Auschecken.',
    'award_display_limit' => 'Preis-Anzeigengrenze',
    'award_display_limit_description' => 'Preis-Anzeigengrenze in der mobilen App.',
    'theme_color'=>'Themenfarbe',
    'records_per_page'=>'Einträge pro Seite',
    'records_per_page_description'=>'Anzeige der Anzahl der Einträge auf der Listenseite.',
];
