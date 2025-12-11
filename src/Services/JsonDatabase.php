<?php

namespace App\Services;

class JsonDatabase
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->ensureFileExists();
    }

    private function ensureFileExists(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public function readAll(): array
    {
        $content = file_get_contents($this->filePath);
        return json_decode($content, true) ?: [];
    }

    public function writeAll(array $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($this->filePath, $json) !== false;
    }

    public function findById(int $id): ?array
    {
        $data = $this->readAll();
        foreach ($data as $item) {
            if (($item['id'] ?? null) === $id) {
                return $item;
            }
        }
        return null;
    }

    public function create(array $item): int
    {
        $data = $this->readAll();
        $newId = $this->getNextId($data);
        $item['id'] = $newId;
        $data[] = $item;
        $this->writeAll($data);
        return $newId;
    }

    public function update(int $id, array $newData): bool
    {
        $data = $this->readAll();
        foreach ($data as $key => $item) {
            if (($item['id'] ?? null) === $id) {
                $data[$key] = array_merge($item, $newData, ['id' => $id]);
                return $this->writeAll($data);
            }
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $data = $this->readAll();
        foreach ($data as $key => $item) {
            if (($item['id'] ?? null) === $id) {
                unset($data[$key]);
                return $this->writeAll(array_values($data));
            }
        }
        return false;
    }

    private function getNextId(array $data): int
    {
        if (empty($data)) {
            return 1;
        }
        $ids = array_column($data, 'id');
        return $ids ? max($ids) + 1 : 1;
    }

    public function findWhere(callable $callback): array
    {
        $data = $this->readAll();
        return array_values(array_filter($data, $callback));
    }
}
