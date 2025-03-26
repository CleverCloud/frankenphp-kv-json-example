<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Demo\Controller\TaskController;
use Demo\Service\TaskService;
use Demo\Service\RedisService;

$config = require_once __DIR__ . '/../config/config.php';

$redisService = new RedisService(
    $config['redis_host'] ?? 'materiakv.eu-fr-1.services.clever-cloud.com',
    $config['redis_port'] ?? 6378,
    $config['redis_password'] ?? null,
    $config['redis_tls'] ?? false
);
$taskService = new TaskService($redisService);
$taskController = new TaskController($taskService);

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        echo $taskController->create($_POST);
        break;

    case 'update':
        $id = $_GET['id'] ?? '';
        if (!$id) {
            header('Location: index.php');
            exit;
        }
        echo $taskController->update($_POST, $id);
        break;

    case 'delete':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $taskController->delete($id);
        }
        header('Location: index.php');
        break;

    case 'toggle':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $taskController->toggleComplete($id);
        }
        header('Location: index.php');
        break;

    case 'index':
    default:
        echo $taskController->index();
        break;
}
