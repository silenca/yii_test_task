<?php

namespace app\components;

use Exception;

class CSVExport {

    protected $filename;
    protected $data;
    protected $csvOptions = ['delimiter' => ','];

    /*
     * @params array $columns
     * @returns void
     */
    public function __construct() {
        $arguments = func_get_args();
        if (!empty($arguments)) {
            foreach ($arguments[0] as $key => $property) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $property;
                }
            }
        }
        if ($this->filename === null) {
            throw new Exception(__CLASS__ . ' filename is required.');
        }
    }
    /*
     * @params array $row
     * @returns void
     */
    public function addRow($row) {
//        $this->data .= '"' . implode('"'.$this->csvOptions['delimiter'].'"', $row) . '"' . "\n";
        $this->data .= implode($this->csvOptions['delimiter'], $row) . "\r\n";
    }
    /*
     * @returns void
     */
    public function export() {
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename="' . $this->filename . '.csv"');
        echo $this->data;
        die();
    }
    public function __toString() {
        return $this->data;
    }
}