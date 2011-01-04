<?php

namespace Behat\Gherkin\Node;

class TableNode
{
    private $rows = array();

    /**
     * Initialize table.
     *
     * @param   string  $table  initial table string
     */
    public function __construct($table = null)
    {
        if (null !== $table) {
            $table = preg_replace("/\r\n|\r/", "\n", $table);

            foreach (explode("\n", $table) as $row) {
                $this->addRow($row);
            }
        }
    }

    /**
     * Add row to the string.
     *
     * @param   string  $row    columns hash (column1 => value, column2 => value)
     */
    public function addRow($row)
    {
        if (is_array($row)) {
            $this->rows[] = $row;
        } else {
            $row = preg_replace("/^\s*\||\|\s*$/", '', $row);

            $this->rows[] = array_map(function($item) {
                return preg_replace("/^\s*|\s*$/", '', $item);
            }, explode('|', $row));
        }
    }

    /**
     * Return table rows.
     *
     * @return  array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Return specific row in a table.
     *
     * @param   integer $rowNum row number
     * 
     * @return  array           columns hash (column1 => value, column2 => value)
     */
    public function getRow($rowNum)
    {
        return $this->rows[$rowNum];
    }

    /**
     * Convert row into delimited string.
     *
     * @param   integer $rowNum row number
     * 
     * @return  string
     */
    public function getRowAsString($rowNum)
    {
        $values = array();
        foreach ($this->getRow($rowNum) as $col => $value) {
            $values[] = $this->padRight(' '.$value.' ', $this->getMaxLengthForColumn($col) + 2);
        }

        return sprintf('|%s|', implode('|', $values));
    }

    /**
     * Replace column value holders with tokens.
     *
     * @param   array   $tokens     hash (search => replace)
     */
    public function replaceTokens(array $tokens)
    {
        foreach ($tokens as $key => $value) {
            foreach (array_keys($this->rows) as $row) {
                foreach (array_keys($this->rows[$row]) as $col) {
                    $this->rows[$row][$col] = str_replace('<'.$key.'>', $value, $this->rows[$row][$col], $count);
                }
            }
        }
    }

    /**
     * Return table hash, formed by columns (ColumnHash).
     *
     * @return  array
     */
    public function getHash()
    {
        $rows = $this->getRows();
        $keys = array_shift($rows);

        $hash = array();
        foreach ($rows as $row) {
            $hash[] = array_combine($keys, $row);
        }

        return $hash;
    }

    /**
     * Return table hash, formed by rows (RowsHash).
     *
     * @return  array
     */
    public function getRowsHash()
    {
        $hash = array();
        $rows = $this->getRows();

        foreach ($this->getRows() as $row) {
            $hash[$row[0]] = $row[1];
        }

        return $hash;
    }

    /**
     * Convert table into string
     *
     * @return  string
     */
    public function __toString()
    {
        $string = '';

        for ($i = 0; $i < count($this->getRows()); $i++) {
            if ('' !== $string) {
                $string .= "\n";
            }
            $string .= $this->getRowAsString($i);
        }

        return $string;
    }

    /**
     * Return max length of specific column.
     *
     * @param   integer $columnNum  column number
     * 
     * @return  integer
     */
    protected function getMaxLengthForColumn($columnNum)
    {
        $max = 0;

        foreach ($this->getRows() as $row) {
            if (($tmp = mb_strlen($row[$columnNum])) > $max) {
                $max = $tmp;
            }
        }

        return $max;
    }

    /**
     * Pad string right.
     *
     * @param   string  $text
     * @param   integer $length
     * 
     * @return  string
     */
    protected function padRight($text, $length)
    {
        while ($length > mb_strlen($text)) {
            $text = $text . ' ';
        }

        return $text;
    }
}
