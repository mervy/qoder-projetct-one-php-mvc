<?php

namespace Kurama\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Database Connection and Query Builder
 * 
 * This class provides database connectivity with PDO and includes
 * a query builder for common database operations.
 */
class Database
{
    /**
     * Database configuration
     * @var array
     */
    private array $config;
    
    /**
     * PDO connection instance
     * @var PDO|null
     */
    private ?PDO $connection = null;
    
    /**
     * Query builder state
     * @var array
     */
    private array $query = [
        'select' => ['*'],
        'from' => null,
        'joins' => [],
        'where' => [],
        'orderBy' => [],
        'groupBy' => [],
        'having' => [],
        'limit' => null,
        'offset' => null
    ];
    
    /**
     * Create new database instance
     * 
     * @param array $config Database configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Get PDO connection instance
     * 
     * @return PDO
     * @throws Exception
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Establish database connection
     * 
     * @return void
     * @throws Exception
     */
    private function connect(): void
    {
        try {
            $dsn = $this->buildDsn();
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options'] ?? []
            );
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Build DSN string from configuration
     * 
     * @return string
     */
    private function buildDsn(): string
    {
        $driver = $this->config['driver'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'] ?? 'utf8mb4';
        
        return "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
    }
    
    /**
     * Execute a raw SQL query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return \PDOStatement
     * @throws Exception
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $connection = $this->getConnection();
            $statement = $connection->prepare($sql);
            $statement->execute($params);
            
            return $statement;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch all rows from query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch single row from query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|false
     */
    public function fetch(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }
    
    /**
     * Execute insert/update/delete query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Number of affected rows
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Get last inserted ID
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }
    
    /**
     * Start query builder - SELECT
     * 
     * @param array|string $columns Columns to select
     * @return self
     */
    public function select($columns = ['*']): self
    {
        $this->resetQuery();
        $this->query['select'] = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    /**
     * Set table for query
     * 
     * @param string $table Table name
     * @return self
     */
    public function from(string $table): self
    {
        $this->query['from'] = $table;
        return $this;
    }
    
    /**
     * Add WHERE condition
     * 
     * @param string $column Column name
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare
     * @param string $boolean Boolean operator (AND/OR)
     * @return self
     */
    public function where(string $column, string $operator, $value, string $boolean = 'AND'): self
    {
        $this->query['where'][] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        return $this;
    }
    
    /**
     * Add OR WHERE condition
     * 
     * @param string $column Column name
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare
     * @return self
     */
    public function orWhere(string $column, string $operator, $value): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }
    
    /**
     * Add ORDER BY clause
     * 
     * @param string $column Column name
     * @param string $direction Sort direction (ASC/DESC)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query['orderBy'][] = "{$column} {$direction}";
        return $this;
    }
    
    /**
     * Add LIMIT clause
     * 
     * @param int $limit Number of rows to limit
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->query['limit'] = $limit;
        return $this;
    }
    
    /**
     * Add OFFSET clause
     * 
     * @param int $offset Number of rows to skip
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->query['offset'] = $offset;
        return $this;
    }
    
    /**
     * Execute query builder and get all results
     * 
     * @return array
     */
    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $params = $this->getQueryParams();
        
        return $this->fetchAll($sql, $params);
    }
    
    /**
     * Execute query builder and get first result
     * 
     * @return array|false
     */
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        
        return !empty($results) ? $results[0] : false;
    }
    
    /**
     * Insert data into table
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int Last insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        
        return (int) $this->lastInsertId();
    }
    
    /**
     * Update data in table
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where WHERE conditions
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, array $where): int
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = :{$column}";
        }
        
        $whereClause = [];
        foreach ($where as $column => $value) {
            $whereClause[] = "{$column} = :where_{$column}";
            $data["where_{$column}"] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . 
               " WHERE " . implode(' AND ', $whereClause);
        
        return $this->execute($sql, $data);
    }
    
    /**
     * Delete data from table
     * 
     * @param string $table Table name
     * @param array $where WHERE conditions
     * @return int Number of affected rows
     */
    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        foreach ($where as $column => $value) {
            $whereClause[] = "{$column} = :{$column}";
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        return $this->execute($sql, $where);
    }
    
    /**
     * Build SELECT query from query builder
     * 
     * @return string
     */
    private function buildSelectQuery(): string
    {
        $sql = "SELECT " . implode(', ', $this->query['select']);
        $sql .= " FROM " . $this->query['from'];
        
        // Add WHERE clauses
        if (!empty($this->query['where'])) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }
        
        // Add ORDER BY
        if (!empty($this->query['orderBy'])) {
            $sql .= " ORDER BY " . implode(', ', $this->query['orderBy']);
        }
        
        // Add LIMIT
        if ($this->query['limit']) {
            $sql .= " LIMIT " . $this->query['limit'];
        }
        
        // Add OFFSET
        if ($this->query['offset']) {
            $sql .= " OFFSET " . $this->query['offset'];
        }
        
        return $sql;
    }
    
    /**
     * Build WHERE clause
     * 
     * @return string
     */
    private function buildWhereClause(): string
    {
        $clauses = [];
        
        foreach ($this->query['where'] as $index => $condition) {
            $clause = "{$condition['column']} {$condition['operator']} :param_{$index}";
            
            if ($index > 0) {
                $clause = "{$condition['boolean']} {$clause}";
            }
            
            $clauses[] = $clause;
        }
        
        return implode(' ', $clauses);
    }
    
    /**
     * Get query parameters for prepared statements
     * 
     * @return array
     */
    private function getQueryParams(): array
    {
        $params = [];
        
        foreach ($this->query['where'] as $index => $condition) {
            $params["param_{$index}"] = $condition['value'];
        }
        
        return $params;
    }
    
    /**
     * Reset query builder state
     * 
     * @return void
     */
    private function resetQuery(): void
    {
        $this->query = [
            'select' => ['*'],
            'from' => null,
            'joins' => [],
            'where' => [],
            'orderBy' => [],
            'groupBy' => [],
            'having' => [],
            'limit' => null,
            'offset' => null
        ];
    }
    
    /**
     * Get database configuration
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}