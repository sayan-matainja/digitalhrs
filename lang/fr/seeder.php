<?php

use Illuminate\Support\Str;

return [

    /** App Setting */

    'authorize-login' => 'Connexion autorisée',
    'override-bssid' => 'Remplacer bssid',
    '24-hour-format' => 'Format 24 heures',
    'bs' => 'Date en BS',
    'attendance-note' => 'Note de présence',

    /** General Setting */

    /** Paramètres généraux */
    'firebase_key' => 'Clé Firebase',
    'firebase_key_description' => 'La clé Firebase est nécessaire pour envoyer des notifications au mobile.',
    'attendance_notify' => 'Définir le nombre de jours pour les notifications push locales',
    'attendance_notify_description' => "Définir le nombre de jours enverra automatiquement les données de ces jours à l'application mobile. La réception de ces données sur le mobile permettra à l'application mobile de configurer des notifications push locales pour ces dates. Les notifications push locales aideront les employés à se souvenir de se connecter à l'heure et également à se déconnecter lorsque le quart est sur le point de se terminer.",
    'advance_salary_limit' => 'Limite de salaire avancé (%)',
    'advance_salary_limit_description' => "Définir le montant maximum en pourcentage qu'un employé peut demander à l'avance en fonction du salaire brut.",
    'employee_code_prefix' => 'Préfixe du code employé',
    'employee_code_prefix_description' => 'Ce préfixe sera utilisé pour créer le code de l’employé.',
    'attendance_limit' => 'Limite de présence',
    'attendance_limit_description' => "Limite de présence pour l'enregistrement et la sortie.",
    'award_display_limit' => "Limite d'affichage des récompenses",
    'award_display_limit_description' => "Limite d'affichage des récompenses dans l'application mobile.",
    'theme_color' => 'Couleur du thème',
    'records_per_page' => 'Enregistrements par page',
    'records_per_page_description' => 'Afficher le nombre d’enregistrements dans la page de liste.',
];
