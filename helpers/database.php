<?php
/**
 * Database Connection Helper
 * 
 * Provides PDO connection with tenant-aware query helpers.
 */

/**
 * Database connection instance (singleton)
 */
$GLOBALS['__db_connection'] = null;

/**
 * Get the database PDO connection
 * 
 * @return PDO
 * @throws PDOException
 */
function db(): PDO
{
    if ($GLOBALS['__db_connection'] !== null) {
        return $GLOBALS['__db_connection'];
    }

    $config = config('database.connections.mysql');
    $options = config('database.options', []);

    $dsn = sprintf(
        '%s:host=%s;port=%d;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );

    $GLOBALS['__db_connection'] = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        $options
    );

    // Sync database session timezone with application timezone
    $tz = config('app.timezone', 'America/Sao_Paulo');
    $GLOBALS['__db_connection']->exec("SET time_zone = '{$tz}'");

    return $GLOBALS['__db_connection'];
}

/**
 * Execute a query and return the statement
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows from a query
 * 
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return array
 */
function db_fetch_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

/**
 * Fetch a single row from a query
 * 
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return array|null
 */
function db_fetch_one(string $sql, array $params = []): ?array
{
    $result = db_query($sql, $params)->fetch();
    return $result === false ? null : $result;
}

/**
 * Fetch a single column value
 * 
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return mixed
 */
function db_fetch_column(string $sql, array $params = [])
{
    return db_query($sql, $params)->fetchColumn();
}

/**
 * Insert a row and return the last insert ID
 * 
 * @param string $table Table name
 * @param array $data Column => value pairs
 * @return int Last insert ID
 */
function db_insert(string $table, array $data): int
{
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');

    $sql = sprintf(
        'INSERT INTO `%s` (`%s`) VALUES (%s)',
        $table,
        implode('`, `', $columns),
        implode(', ', $placeholders)
    );

    db_query($sql, array_values($data));
    return (int) db()->lastInsertId();
}

/**
 * Update rows in a table
 * 
 * @param string $table Table name
 * @param array $data Column => value pairs to update
 * @param string $where WHERE clause
 * @param array $whereParams Parameters for WHERE clause
 * @return int Number of affected rows
 */
function db_update(string $table, array $data, string $where, array $whereParams = []): int
{
    $setParts = [];
    $values = [];

    foreach ($data as $column => $value) {
        $setParts[] = "`$column` = ?";
        $values[] = $value;
    }

    $sql = sprintf(
        'UPDATE `%s` SET %s WHERE %s',
        $table,
        implode(', ', $setParts),
        $where
    );

    return db_query($sql, array_merge($values, $whereParams))->rowCount();
}

/**
 * Delete rows from a table
 * 
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return int Number of deleted rows
 */
function db_delete(string $table, string $where, array $params = []): int
{
    $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
    return db_query($sql, $params)->rowCount();
}

/**
 * Begin a database transaction
 */
function db_begin(): void
{
    db()->beginTransaction();
}

/**
 * Commit a database transaction
 */
function db_commit(): void
{
    db()->commit();
}

/**
 * Rollback a database transaction
 */
function db_rollback(): void
{
    if (db()->inTransaction()) {
        db()->rollBack();
    }
}

/**
 * Check if currently in a transaction
 */
function db_in_transaction(): bool
{
    return db()->inTransaction();
}

/**
 * Close the database connection
 */
function db_close(): void
{
    $GLOBALS['__db_connection'] = null;
}
