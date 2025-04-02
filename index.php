<?php
require_once 'Tree.php';

$dsn = 'pgsql:host=localhost;dbname=postgres';
$user = 'a11';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tree = new Tree($pdo);

    echo "<h1>Работа с иерархической структурой</h1>";

    echo "<h2>Добавление элементов</h2>";
    $root1 = $tree->addNode('Уровень 1');
    echo "Добавлен корневой узел с ID: $root1<br>";

    $child11 = $tree->addNode('Потомок 1.1', $root1);
    echo "Добавлен узел с ID: $child11<br>";

    $child12 = $tree->addNode('Потомок 1.2', $root1);
    echo "Добавлен узел с ID: $child12<br>";

    $child121 = $tree->addNode('Потомок 1.2.1', $child12);
    echo "Добавлен узел с ID: $child121<br>";

    $child1211 = $tree->addNode('Потомок 1.2.1.1', $child121);
    echo "Добавлен узел с ID: $child1211<br>";

    $root2 = $tree->addNode('Уровень 2');
    echo "Добавлен корневой узел с ID: $root2<br>";

    $child21 = $tree->addNode('Потомок 2.1', $root2);
    echo "Добавлен узел с ID: $child21<br>";

    echo "<h2>Форматированное дерево</h2>";
    $formattedTree = $tree->getFormattedTree();
    foreach ($formattedTree as $node) {
        echo htmlspecialchars($node['name']) . " (ID: {$node['id']}, уровень: {$node['level']})<br>";
    }

//    echo "<h2>Плоский список всех узлов</h2>";
//    echo "<pre>";
//    print_r($tree->getFlatList());
//    echo "</pre>";

    echo "<h2>Потомки узла 1.2</h2>";
    $descendants = $tree->getDescendants($child12);
    foreach ($descendants as $node) {
        echo htmlspecialchars($node['name']) . " (ID: {$node['id']}, уровень: {$node['level']})<br>";
    }

    echo "<h2>Путь к узлу 1.2.1</h2>";
    $path = $tree->getPathToNode($child121);
    foreach ($path as $index => $node) {
        echo ($index > 0 ? " → " : "") . htmlspecialchars($node['name']);
    }
    echo "<br>";

//    echo "<h2>Удаление узла 1.2 и всех его потомков</h2>";
//    $tree->deleteNode($child12);
//    echo "Узел удален<br>";
//
//    echo "<h2>Дерево после удаления</h2>";
//    $formattedTree = $tree->getFormattedTree();
//    foreach ($formattedTree as $node) {
//        echo htmlspecialchars($node['name']) . "<br>";
//    }

} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage();
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}