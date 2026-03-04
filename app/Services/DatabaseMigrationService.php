<?php
/**
 * Database Migration Service
 * 
 * Provides native PHP capabilities to export and import SQL dumps
 * without relying on external system tools like mysqldump.
 */

namespace App\Services;

use PDO;
use Exception;
use RuntimeException;

class DatabaseMigrationService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
        
        if (!$this->pdo instanceof PDO) {
            throw new RuntimeException("Falha ao obter conexão com o banco de dados.");
        }
    }

    /**
     * Export the database to a SQL string
     * 
     * @return string SQL dump
     */
    public function exportDatabase(): string
    {
        $sql = "-- DesbravaHub Database Dump\n";
        $sql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "START TRANSACTION;\n\n";

        $tables = $this->getTables();

        // 1. Drop all tables first to avoid FK compatibility issues with existing tables
        $sql .= "-- Drop existing tables\n";
        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        }
        $sql .= "\n";

        // 2. Create tables and export data
        foreach ($tables as $table) {
            $sql .= "-- Structure for table `{$table}`\n";
            $createTableQuery = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $sql .= $createTableQuery['Create Table'] . ";\n\n";

            // Export data using unbuffered or chunked selection to save memory
            $sql .= "-- Data for table `{$table}`\n";
            
            $offset = 0;
            $limit = 500;
            
            while (true) {
                // Fetch in chunks to avoid memory exhaustion
                $rows = $this->pdo->query("SELECT * FROM `{$table}` LIMIT {$limit} OFFSET {$offset}")->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($rows)) {
                    break;
                }

                $insertQuery = $this->buildInsertQuery($table, $rows);
                $sql .= $insertQuery . ";\n";
                
                $offset += $limit;
                
                // If we fetched fewer rows than the limit, we're done with this table
                if (count($rows) < $limit) {
                    break;
                }
            }
            $sql .= "\n";
        }

        $sql .= "COMMIT;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * Import a SQL dump from a file path
     * 
     * @param string $filePath Path to the uploaded .sql file
     * @return bool True if successful
     * @throws Exception If errors occur during execution
     */
    public function importDatabase(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Arquivo de importação não encontrado.");
        }

        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new RuntimeException("Não foi possível abrir o arquivo de importação.");
        }

        try {
            $this->pdo->exec("SET NAMES utf8mb4");
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0");

            $query = "";
            while (($line = fgets($handle)) !== false) {
                // Skip comments
                if (str_starts_with(trim($line), "--") || str_starts_with(trim($line), "/*") || empty(trim($line))) {
                    continue;
                }

                $query .= $line;

                // Check if the query is finished (ends with a semicolon)
                // This is still a bit primitive for complex triggers/procedures, but works for standard dumps
                if (str_ends_with(trim($line), ";")) {
                    $this->pdo->exec($query);
                    $query = "";
                }
            }

            fclose($handle);
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            return true;
        } catch (Exception $e) {
            if ($handle) fclose($handle);
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            throw new RuntimeException("Erro ao importar banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Get all tables in the database
     */
    private function getTables(): array
    {
        $stmt = $this->pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Build a bulk INSERT query for a chunk of rows
     */
    private function buildInsertQuery(string $table, array $rows): string
    {
        $columns = array_keys($rows[0]);
        $columnsStr = implode(', ', array_map(fn($col) => "`{$col}`", $columns));

        $valuesList = [];
        foreach ($rows as $row) {
            $rowValues = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $rowValues[] = 'NULL';
                } elseif (is_numeric($val) && !is_string($val)) {
                    $rowValues[] = $val;
                } else {
                    // Properly escape strings
                    $rowValues[] = $this->pdo->quote((string)$val);
                }
            }
            $valuesList[] = '(' . implode(', ', $rowValues) . ')';
        }

        $valuesStr = implode(', ', $valuesList);
        return "INSERT INTO `{$table}` ({$columnsStr}) VALUES {$valuesStr}";
    }
}
