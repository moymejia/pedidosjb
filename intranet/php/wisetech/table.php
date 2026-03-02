<?php
require_once 'mysql.php';
require_once 'array_functions.php';
require_once 'objects.php';
class table extends mysql
{
    use array_functions, objects;
    private $db    = "";
    private $table = "";

    public function __construct($db, $table)
    {
        parent::__construct($db); //inicia conexion de base de datos
        $this->db    = $db; //configura base de datso con la que trabajara la clase
        $this->table = $table; //configura tabla con la que trabajara la clase
    }

    public function insert_record($DATA)
    { //inserta un registro nuevo en la base de datos y tabla actual, con los valores del arreglo recibido
        $DATA   = $this->sanitize_array($DATA); //sanitiza valores de los campos.
        $fields = "";
        $values = "";
        foreach ($DATA as $key => $value) {
            $fields .= $key . ",";
            if ($value == 'NULL') {
                $values .= " NULL ,";
            } else {
                $values .= "'" . $value . "',";
            }
        }
        $fields = rtrim($fields, ","); //elimina comas sobrantes.
        $values = rtrim($values, ","); //elimina comas sobranees.

        return mysql::put("INSERT INTO " . $this->db . "." . $this->table . " ($fields) VALUES ($values) "); //ejecuta insercion.
    }

    public function replace_record($DATA)
    { //inserta un registro nuevo en la base de datos y tabla actual, con los valores del arreglo recibido
        $DATA   = $this->sanitize_array($DATA); //sanitiza valores de los campos.
        $fields = "";
        $values = "";
        foreach ($DATA as $key => $value) {
            $fields .= $key . ",";
            if ($value == 'NULL') {
                $values .= " NULL ,";
            } else {
                $values .= "'" . $value . "',";
            }
        }
        $fields = rtrim($fields, ","); //elimina comas sobrantes.
        $values = rtrim($values, ","); //elimina comas sobranees.

        return mysql::put("REPLACE INTO " . $this->db . "." . $this->table . " ($fields) VALUES ($values) "); //ejecuta insercion.
    }

    public function update_record($DATA, $KEYS)
    { //actualiza un registro con los valores del primer arreglo y las condiciones del segundo
        $DATA   = $this->sanitize_array($DATA); //sanitiza valores de los campos.
        $KEYS   = $this->sanitize_array($KEYS); //sanitiza valores de los campos.
        $fields = "";
        $keys   = "";
        foreach ($DATA as $key => $value) {
            if ($value == "NULL") {
                $fields .= " $key = NULL,";
            } else {
                $fields .= " $key = '$value',";
            }
        }
        foreach ($KEYS as $key) {
            if ($DATA[$key] == "NULL") {
                $keys .= " AND $key IS NULL ";
            } else {
                $keys .= " AND $key = '{$DATA[$key]}' ";
            }
        }
        $fields = rtrim($fields, ","); //elimina comas restantes

        return mysql::put("UPDATE " . $this->db . "." . $this->table . " SET $fields WHERE 1=1 $keys "); //ejecuta actualizacion
    }

    public function delete_record($DATA)
    {
        $DATA  = $this->sanitize_array($DATA); //sanitiza valores de los campos.
        $where = "";
        foreach ($DATA as $key => $value) {
            if ($value == "NULL") {
                $where .= " AND $key IS NULL ";
            } else {
                $where .= " AND $key = '$value' ";
            }
        }

        return mysql::put("DELETE FROM " . $this->db . "." . $this->table . " WHERE 1=1 $where "); //ejecuta eliminacion
    }

}
