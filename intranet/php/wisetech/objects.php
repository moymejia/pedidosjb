<?php
trait objects{
    public static function get_object($relative_path, $table = ''){
        require_once($relative_path.$table.".php");
        $objeto = new $table();
        return $objeto;
    } 
}
?>