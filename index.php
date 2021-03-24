<?php

use Meleshun\HumanResourcesManager;
use Meleshun\JuniorDeveloper;
use Meleshun\Manager;
use Meleshun\TeamLeader;

require __DIR__ . '/vendor/autoload.php';

$terminatorT70 = new TeamLeader([
    'Хорошее настроение',
    'Нормальное настроение',
    'Плохое настроение',
    'Настроение «Не попадайся на глаза»'
]);

// Подписать менеджеров на события
$terminatorT70->attach(new HumanResourcesManager(), 'developer:rebuke');
$terminatorT70->attach(new Manager(), 'developer:praise');

// Проверить выполнение работы JuniorDeveloper (true || false)
$terminatorT70->checkTaskCompletion(JuniorDeveloper::getWorkStatus());