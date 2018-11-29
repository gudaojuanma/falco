<?php

namespace App\Core\Db\Dialect;

use Phalcon\Db\Exception;
use Phalcon\Db\Dialect\Mysql as BaseMysql;

class Mysql extends BaseMysql
{
    public function createTable($tableName, $schemaName, array $definition)
    {
        if (!isset($definition['columns'])) {
            throw new Exception("The index 'columns' is required in the definition array");
        }

        $columns = $definition['columns'];

        $table = $this->prepareTable($tableName, $schemaName);

        $temporary = false;
        if (isset($definition['options']) && isset($definition['options']['temporary'])) {
            $temporary = $definition['options']['temporary'];
        }

        if ($temporary) {
            $sql = "CREATE TEMPORARY TABLE {$table} (\n\t";
        } else {
            $sql = "CREATE TABLE {$table} (\n\t";
        }

        $createLines = [];
        foreach ($columns as $column) {
            $columnLine = sprintf("`%s` %s", $column->getName(), $this->getColumnDefinition($column));

            if ($column->hasDefault()) {
                $defaultValue = $column->getDefault();
                if (strstr(strtolower($defaultValue), 'CURRENT_TIMESTAMP')) {
                    $columnLine .= " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $columnLine .= sprintf(" DEFAULT '%s'", addcslashes($defaultValue, "'"));
                }
            }

            if ($column->isNotNull()) {
                $columnLine .= " NOT NULL";
            }

            if ($column->isAutoIncrement()) {
                $columnLine .= " AUTO_INCREMENT";
            }

            if ($column->isPrimary()) {
                $columnLine .= " PRIMARY KEY";
            }

            if (($comment = $column->getComment())) {
                $columnLine .= sprintf(" COMMENT '%s'", addcslashes($comment, "'"));
            }

            $createLines[] = $columnLine;
        }

        if (isset($definition['indexes'])) {
            foreach ($definition['indexes'] as $index) {
                $indexName = $index->getName();
                $indexType = $index->getType();

                if ($indexName == 'PRIMARY') {
                    $indexSql = sprintf("PRIMARY KEY (%s)", $this->getColumnList($index->getColumns()));
                } else {
                    if (empty($indexType)) {
                        $indexSql = sprintf("KEY `%s` (%s)", $indexName, $this->getColumnList($index->getColumns()));
                    } else {
                        $indexSql = sprintf("%s KEY `%s` (%s)", $indexType, $indexName, $this->getColumnList($index->getColumns()));
                    }
                }

                $createLines[] = $indexSql;
            }
        }

        if (isset($definition['references'])) {
            foreach ($definition['references'] as $reference) {
                $referenceSql = sprintf("CONSTRAINT `%s` FOREIGN KEY (%s) REFERENCES `%s` (%s)", $reference->getName(), $this->getColumnList($reference->getColumns()), $reference->getReferencedTable(), $this->getColumnList($reference->getReferencedColumns()));

                $onDelete = $reference->getOnDelete();
                if (!empty($onDelete)) {
                    $referenceSql .= sprintf(" ON DELETE %s", $onDelete);
                }

                $onUpdate = $reference->getOnUpdate();
                if (!empty($onUpdate)) {
                    $referenceSql .= sprintf(" ON UPDATE %s", $onUpdate);
                }

                $createLines[] = $referenceSql;
            }
        }

        $sql .= join(",\n\t", $createLines) . "\n)";

        if (isset($definition['options'])) {
            $sql .= " " . $this->_getTableOptions($definition);
        }

        return $sql;
    }
}
