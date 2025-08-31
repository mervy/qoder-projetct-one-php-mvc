<?php

namespace Kurama\Core;

use Exception;

/**
 * Base Model Class
 * 
 * This abstract class provides ORM functionality for database models.
 * It includes common database operations and relationship management.
 */
abstract class Model
{
    /**
     * Database connection instance
     * @var Database
     */
    protected static Database $database;
    
    /**
     * Table name (should be overridden in child classes)
     * @var string
     */
    protected string $table = '';
    
    /**
     * Primary key column name
     * @var string
     */
    protected string $primaryKey = 'id';
    
    /**
     * Fillable attributes
     * @var array
     */
    protected array $fillable = [];
    
    /**
     * Hidden attributes (not included in toArray)
     * @var array
     */
    protected array $hidden = [];
    
    /**
     * Model attributes
     * @var array
     */
    protected array $attributes = [];
    
    /**
     * Original attributes (for tracking changes)
     * @var array
     */
    protected array $original = [];
    
    /**
     * Indicates if the model exists in database
     * @var bool
     */
    protected bool $exists = false;
    
    /**
     * Timestamps columns
     * @var bool
     */
    protected bool $timestamps = true;
    
    /**
     * Created at column name
     * @var string
     */
    protected string $createdAt = 'created_at';
    
    /**
     * Updated at column name
     * @var string
     */
    protected string $updatedAt = 'updated_at';
    
    /**
     * Create new model instance
     * 
     * @param array $attributes Initial attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);\n        \n        if (empty($this->table)) {\n            $this->table = $this->getDefaultTableName();\n        }\n    }\n    \n    /**\n     * Set database instance\n     * \n     * @param Database $database\n     * @return void\n     */\n    public static function setDatabase(Database $database): void\n    {\n        static::$database = $database;\n    }\n    \n    /**\n     * Get database instance\n     * \n     * @return Database\n     * @throws Exception\n     */\n    protected static function getDatabase(): Database\n    {\n        if (!isset(static::$database)) {\n            throw new Exception('Database not set for model');\n        }\n        \n        return static::$database;\n    }\n    \n    /**\n     * Get default table name from class name\n     * \n     * @return string\n     */\n    protected function getDefaultTableName(): string\n    {\n        $className = basename(str_replace('\\\\', '/', static::class));\n        // Convert PascalCase to snake_case and pluralize\n        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));\n        \n        // Simple pluralization (add 's' or 'es')\n        if (substr($tableName, -1) === 'y') {\n            $tableName = substr($tableName, 0, -1) . 'ies';\n        } elseif (in_array(substr($tableName, -1), ['s', 'x', 'z']) || \n                  in_array(substr($tableName, -2), ['ch', 'sh'])) {\n            $tableName .= 'es';\n        } else {\n            $tableName .= 's';\n        }\n        \n        return $tableName;\n    }\n    \n    /**\n     * Fill model attributes\n     * \n     * @param array $attributes\n     * @return self\n     */\n    public function fill(array $attributes): self\n    {\n        foreach ($attributes as $key => $value) {\n            if ($this->isFillable($key)) {\n                $this->setAttribute($key, $value);\n            }\n        }\n        \n        return $this;\n    }\n    \n    /**\n     * Check if attribute is fillable\n     * \n     * @param string $key\n     * @return bool\n     */\n    protected function isFillable(string $key): bool\n    {\n        return empty($this->fillable) || in_array($key, $this->fillable);\n    }\n    \n    /**\n     * Set attribute value\n     * \n     * @param string $key\n     * @param mixed $value\n     * @return void\n     */\n    public function setAttribute(string $key, $value): void\n    {\n        $this->attributes[$key] = $value;\n    }\n    \n    /**\n     * Get attribute value\n     * \n     * @param string $key\n     * @param mixed $default\n     * @return mixed\n     */\n    public function getAttribute(string $key, $default = null)\n    {\n        return $this->attributes[$key] ?? $default;\n    }\n    \n    /**\n     * Magic getter for attributes\n     * \n     * @param string $key\n     * @return mixed\n     */\n    public function __get(string $key)\n    {\n        return $this->getAttribute($key);\n    }\n    \n    /**\n     * Magic setter for attributes\n     * \n     * @param string $key\n     * @param mixed $value\n     * @return void\n     */\n    public function __set(string $key, $value): void\n    {\n        $this->setAttribute($key, $value);\n    }\n    \n    /**\n     * Magic isset for attributes\n     * \n     * @param string $key\n     * @return bool\n     */\n    public function __isset(string $key): bool\n    {\n        return isset($this->attributes[$key]);\n    }\n    \n    /**\n     * Find model by primary key\n     * \n     * @param mixed $id\n     * @return static|null\n     */\n    public static function find($id): ?static\n    {\n        $instance = new static();\n        \n        $result = static::getDatabase()\n            ->select()\n            ->from($instance->table)\n            ->where($instance->primaryKey, '=', $id)\n            ->first();\n        \n        if ($result) {\n            return static::newFromBuilder($result);\n        }\n        \n        return null;\n    }\n    \n    /**\n     * Find model by primary key or throw exception\n     * \n     * @param mixed $id\n     * @return static\n     * @throws Exception\n     */\n    public static function findOrFail($id): static\n    {\n        $model = static::find($id);\n        \n        if (!$model) {\n            throw new Exception(\"Model not found with ID: {$id}\");\n        }\n        \n        return $model;\n    }\n    \n    /**\n     * Get all models\n     * \n     * @return array\n     */\n    public static function all(): array\n    {\n        $instance = new static();\n        \n        $results = static::getDatabase()\n            ->select()\n            ->from($instance->table)\n            ->get();\n        \n        return array_map(fn($result) => static::newFromBuilder($result), $results);\n    }\n    \n    /**\n     * Start query builder for model\n     * \n     * @return Database\n     */\n    public static function query(): Database\n    {\n        $instance = new static();\n        \n        return static::getDatabase()\n            ->select()\n            ->from($instance->table);\n    }\n    \n    /**\n     * Find by specific column\n     * \n     * @param string $column\n     * @param mixed $value\n     * @return array\n     */\n    public static function where(string $column, string $operator, $value): array\n    {\n        $instance = new static();\n        \n        $results = static::getDatabase()\n            ->select()\n            ->from($instance->table)\n            ->where($column, $operator, $value)\n            ->get();\n        \n        return array_map(fn($result) => static::newFromBuilder($result), $results);\n    }\n    \n    /**\n     * Create new model instance from database result\n     * \n     * @param array $attributes\n     * @return static\n     */\n    public static function newFromBuilder(array $attributes): static\n    {\n        $model = new static();\n        $model->attributes = $attributes;\n        $model->original = $attributes;\n        $model->exists = true;\n        \n        return $model;\n    }\n    \n    /**\n     * Create new model in database\n     * \n     * @param array $attributes\n     * @return static\n     */\n    public static function create(array $attributes): static\n    {\n        $model = new static($attributes);\n        $model->save();\n        \n        return $model;\n    }\n    \n    /**\n     * Save model to database\n     * \n     * @return bool\n     */\n    public function save(): bool\n    {\n        if ($this->exists) {\n            return $this->performUpdate();\n        } else {\n            return $this->performInsert();\n        }\n    }\n    \n    /**\n     * Perform insert operation\n     * \n     * @return bool\n     */\n    protected function performInsert(): bool\n    {\n        if ($this->timestamps) {\n            $now = date('Y-m-d H:i:s');\n            $this->setAttribute($this->createdAt, $now);\n            $this->setAttribute($this->updatedAt, $now);\n        }\n        \n        $attributes = $this->getAttributesForInsert();\n        \n        $id = static::getDatabase()->insert($this->table, $attributes);\n        \n        $this->setAttribute($this->primaryKey, $id);\n        $this->original = $this->attributes;\n        $this->exists = true;\n        \n        return true;\n    }\n    \n    /**\n     * Perform update operation\n     * \n     * @return bool\n     */\n    protected function performUpdate(): bool\n    {\n        if ($this->timestamps) {\n            $this->setAttribute($this->updatedAt, date('Y-m-d H:i:s'));\n        }\n        \n        $attributes = $this->getAttributesForUpdate();\n        \n        if (empty($attributes)) {\n            return true; // No changes to save\n        }\n        \n        $affected = static::getDatabase()->update(\n            $this->table,\n            $attributes,\n            [$this->primaryKey => $this->getAttribute($this->primaryKey)]\n        );\n        \n        $this->original = $this->attributes;\n        \n        return $affected > 0;\n    }\n    \n    /**\n     * Delete model from database\n     * \n     * @return bool\n     */\n    public function delete(): bool\n    {\n        if (!$this->exists) {\n            return false;\n        }\n        \n        $affected = static::getDatabase()->delete(\n            $this->table,\n            [$this->primaryKey => $this->getAttribute($this->primaryKey)]\n        );\n        \n        $this->exists = false;\n        \n        return $affected > 0;\n    }\n    \n    /**\n     * Get attributes for insert operation\n     * \n     * @return array\n     */\n    protected function getAttributesForInsert(): array\n    {\n        $attributes = $this->attributes;\n        \n        // Remove primary key if it's null or empty\n        if (empty($attributes[$this->primaryKey])) {\n            unset($attributes[$this->primaryKey]);\n        }\n        \n        return $attributes;\n    }\n    \n    /**\n     * Get attributes for update operation (only changed attributes)\n     * \n     * @return array\n     */\n    protected function getAttributesForUpdate(): array\n    {\n        $attributes = [];\n        \n        foreach ($this->attributes as $key => $value) {\n            if ($key === $this->primaryKey) {\n                continue; // Skip primary key\n            }\n            \n            if (!isset($this->original[$key]) || $this->original[$key] !== $value) {\n                $attributes[$key] = $value;\n            }\n        }\n        \n        return $attributes;\n    }\n    \n    /**\n     * Check if model has been modified\n     * \n     * @return bool\n     */\n    public function isDirty(): bool\n    {\n        return !empty($this->getAttributesForUpdate());\n    }\n    \n    /**\n     * Convert model to array\n     * \n     * @return array\n     */\n    public function toArray(): array\n    {\n        $attributes = $this->attributes;\n        \n        // Remove hidden attributes\n        foreach ($this->hidden as $hidden) {\n            unset($attributes[$hidden]);\n        }\n        \n        return $attributes;\n    }\n    \n    /**\n     * Convert model to JSON\n     * \n     * @return string\n     */\n    public function toJson(): string\n    {\n        return json_encode($this->toArray());\n    }\n    \n    /**\n     * Get table name\n     * \n     * @return string\n     */\n    public function getTable(): string\n    {\n        return $this->table;\n    }\n    \n    /**\n     * Get primary key name\n     * \n     * @return string\n     */\n    public function getKeyName(): string\n    {\n        return $this->primaryKey;\n    }\n    \n    /**\n     * Get primary key value\n     * \n     * @return mixed\n     */\n    public function getKey()\n    {\n        return $this->getAttribute($this->primaryKey);\n    }\n}