<?php

if (isset($_GET['test']) && is_file($test = __DIR__ . D . 'test' . D . basename((string) $_GET['test']) . '.php')) {
    $sub = trim(strtr(PATH . D, [rtrim(strtr($_SERVER['DOCUMENT_ROOT'], '/', D), D) . D => ""]), D);
    require $test;
} else {
    echo '<ul>';
    foreach (glob(__DIR__ . D . 'test' . D . '*.php') as $v) {
        echo '<li>';
        echo '<a href="?test=' . basename($v, '.php') . '">';
        echo strtr($v, [PATH => '.']);
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}