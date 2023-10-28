<?php

return [
    'class' => yii\db\Connection::class,
    'dsn' => 'pgsql:' .
        'host=' . '172.21.0.2' . ';' .
        'dbname=' . 'aylo',
    'username' => 'postgres',
    'password' => 'postgres',

    'enableSchemaCache' => true,
    'charset' => 'utf8',
    'attributes' => [
        PDO::ATTR_PERSISTENT => true,
    ]
];
