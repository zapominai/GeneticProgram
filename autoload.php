<?php

require_once 'config.php';

function autoload($class) {
    foreach (['classes'] as $dir) {
        if (!is_file($file = sprintf('%s/%s/%s.php', dirname(__FILE__), $dir, $class))) continue;

        require_once $file;
    }
}

if (version_compare(phpversion(), '5.1.2', '>=')) {
    spl_autoload_register('autoload');
} else {
    die(sprintf('phpversion is %s. Need 5.1.2 >=', phpversion()));
}

try {
    $pdo = new PDO(sprintf('mysql:host=localhost;dbname=%s', $dbConfig['name']), $dbConfig['login'], $dbConfig['pass']);
} catch (PDOException $e) {
    log::error('[PDO] Ошибка при подключении к "%s": %s', $dbConfig['name'], $e->getMessage());
}