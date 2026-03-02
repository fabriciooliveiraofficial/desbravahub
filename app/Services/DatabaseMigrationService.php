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
        $sql .= "START TRANSACTION;\n\n";

        $tables = $this->getTables();

        foreach ($tables as $table) {
            // Drop table
            $sql .= "-- Table structure for table `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            // Create table
            $createTableQuery = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $sql .= $createTableQuery['Create Table'] . ";\n\n";

            // Export data
            $rows = $this->pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $sql .= "-- Dumping data for table `{$table}`\n";
                $chunks = array_chunk($rows, 100); // 100 rows per insert statement

                foreach ($chunks as $chunk) {
                    $insertQuery = $this->buildInsertQuery($table, $chunk);
                    $sql .= $insertQuery . ";\n";
                }
                $sql .= "\n";
            }
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

        $sql = file_get_contents($filePath);
        if (empty(trim($sql))) {
            throw new RuntimeException("O arquivo de importação está vazio.");
        }

        try {
            // We disable foreign key checks inside the script normally, 
            // but let's enforce it on the PDO connection just in case the transaction fails
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0");
            
            // Execute the raw dump
            // Note: PDO::exec can handle multiple statements if configured, 
            // but for large dumps it's better to run it. By default MySQL driver supports multistatement if emulated.
            $this->pdo->exec($sql);
            
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1");
            return true;
        } catch (Exception $e) {
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
