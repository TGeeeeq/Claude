<?php
/**
 * Hlavní konfigurační soubor pro citlivé údaje.
 * Tento soubor je chráněn pravidlem v .htaccess.
 */

return [
    'smtp' => [
        'Host'       => 'smtp.forpsi.com',
        'SMTPAuth'   => true,
        'Username'   => 'info@nechmerust.org',
        'Password'   => 'E6MF6F-GtC', // Zde uložte své skutečné heslo k emailu
        'SMTPSecure' => 'tls', 
        'Port'       => 587,
        'FromEmail'  => 'info@nechmerust.org',
        'FromName'   => 'Nech mě růst',
    ],
    'database' => [
        'host' => 'a046um.forpsi.com',
        'name' => 'f193818',
        'user' => 'f193818',
        'pass' => '9GubTB_b', // Zde uložte své skutečné heslo k databázi
    ]
];
?>
