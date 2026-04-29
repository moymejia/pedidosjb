<?php
require_once '../wisetech/table.php';
require_once '../wisetech/utils.php';

class tipo_documento extends table
{
    use utils;

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'tipo_documento');
    }

    public function option_activas()
    {
        return mysql::getoptions("SELECT idtipo_documento AS id, nombre AS descripcion FROM tipo_documento WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
    }

    public function obtener_por_id($idtipo_documento)
    {
        return mysql::getrow("SELECT idtipo_documento, nombre, correlativo, estado FROM tipo_documento WHERE idtipo_documento = '$idtipo_documento' LIMIT 1");
    }
}
