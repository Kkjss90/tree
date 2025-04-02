<?php
class Tree {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function addNode(string $name, ?int $parentId = null): int {
        $stmt = $this->pdo->prepare("INSERT INTO tree_nodes (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parentId]);
        return $this->pdo->lastInsertId();
    }

    public function deleteNode(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM tree_nodes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getFullTree(?int $parentId = null): array {
        $stmt = $this->pdo->prepare("SELECT id, name, parent_id FROM tree_nodes WHERE parent_id " .
            ($parentId === null ? "IS NULL" : "= ?") . " ORDER BY id");
        $stmt->execute($parentId === null ? [] : [$parentId]);

        $tree = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $node = [
                'id' => $row['id'],
                'name' => $row['name'],
                'parent_id' => $row['parent_id'],
                'children' => $this->getFullTree($row['id'])
            ];
            $tree[] = $node;
        }
        return $tree;
    }

    public function getFormattedTree(?int $parentId = null, int $level = 0): array {
        $tree = [];
        $stmt = $this->pdo->prepare("SELECT id, name FROM tree_nodes WHERE parent_id " .
            ($parentId === null ? "IS NULL" : "= ?") . " ORDER BY id");
        $stmt->execute($parentId === null ? [] : [$parentId]);

        while ($node = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tree[] = [
                'id' => $node['id'],
                'name' => str_repeat('--', $level) . ' ' . $node['name'],
                'level' => $level
            ];
            $tree = array_merge($tree, $this->getFormattedTree($node['id'], $level + 1));
        }

        return $tree;
    }

    public function getDescendants(int $parentId): array {
        $descendants = [];
        $this->fetchDescendants($parentId, $descendants);
        return $descendants;
    }

    private function fetchDescendants(int $parentId, array &$descendants, int $level = 1): void {
        $stmt = $this->pdo->prepare("SELECT id, name FROM tree_nodes WHERE parent_id = ? ORDER BY id");
        $stmt->execute([$parentId]);

        while ($node = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $descendants[] = [
                'id' => $node['id'],
                'name' => str_repeat('--', $level) . ' ' . $node['name'],
                'level' => $level
            ];
            $this->fetchDescendants($node['id'], $descendants, $level + 1);
        }
    }

    public function getFlatList(): array {
        $stmt = $this->pdo->query("SELECT id, name, parent_id FROM tree_nodes ORDER BY parent_id, id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPathToNode(int $nodeId): array {
        $path = [];
        $currentId = $nodeId;

        while ($currentId !== null) {
            $stmt = $this->pdo->prepare("SELECT id, name, parent_id FROM tree_nodes WHERE id = ?");
            $stmt->execute([$currentId]);
            $node = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$node) break;

            array_unshift($path, [
                'id' => $node['id'],
                'name' => $node['name']
            ]);

            $currentId = $node['parent_id'];
        }

        return $path;
    }
}