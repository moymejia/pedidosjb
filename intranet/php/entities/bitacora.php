<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
class bitacora extends table
{
    use utils;
    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_seguridad', 'bitacora');

        $this->ACCIONES['opcion']             = 16;
        $this->ACCIONES['consultar_bitacora'] = "Consultar_bitacora";

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'consultar_bitacora') {
                if (table::validate_parameter_existence(['usuario', 'fecha_desde', 'fecha_hasta'], $PARAMETROS)) {
                    if ($resultado = self::consultar_bitacora($PARAMETROS['usuario'], $PARAMETROS['fecha_desde'], $PARAMETROS['fecha_hasta'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parametros incompletos");
                }
            }

        }
    }

    public function cargar_opcion()
    {
        $DATA                     = [];
        $DATA['usuarios_activos'] = mysql::getoptions("SELECT usuario id, nombre descripcion FROM usuario WHERE estado = 'ACTIVO'");
        // $result = mysql::getresult("SELECT idbitacora, nombre, estado FROM bitacora ");

        $html = new html('bitacora', $DATA);

        return $html->get_html();
    }

    public function consultar_bitacora($usuario, $fecha_desde, $fecha_hasta)
    {
        //validar permisos
        $security = new security($this->ACCIONES['consultar_bitacora']);
        //inicializar reporte
        require_once 'report.php';
        $PARAMETROS          = [];
        $PARAMETROS['print'] = true;
        $PARAMETROS['excel'] = true;
        $report              = new report("Registros de bitacora", $PARAMETROS);

        $fecha_desde_str = ($fecha_desde != '') ? date('d-m-Y', strtotime($fecha_desde)) : "";
        $fecha_hasta_str = ($fecha_hasta != '') ? date('d-m-Y', strtotime($fecha_hasta)) : "";
        if ($fecha_desde == '' && $usuario == '') {
            $this->last_error = 'Parametros incorrectos, se debe indicar rol de usuario o fecha desde.';

            return false;
        }

        $filtro_fecha = ($fecha_hasta == '') ? "fecha = '$fecha_desde'" : " fecha >= '$fecha_desde' AND fecha <= '$fecha_hasta' ";
        $filtro_rol   = ($usuario != '') ? " AND usuario = '$usuario' " : "";

        $report->addTitle("Bitacora de operaciones");

        $result = mysql::getresult("SELECT fecha, hora,  nombre_usuario usuario, REPLACE(opcion, '_', ' ') opcion, REPLACE(accion, '_', ' ') accion, referencia_1, referencia_2, referencia_3
            FROM view_bitacora
            WHERE $filtro_fecha  $filtro_rol
            ORDER BY idbitacora DESC");

        $aligments['fecha']        = 'center';
        $aligments['hora']         = 'center';
        $aligments['usuario']      = 'left';
        $aligments['opcion']       = 'left';
        $aligments['accion']       = 'left';
        $aligments['referencia_1'] = 'left';
        $aligments['referencia_2'] = 'left';
        $aligments['referencia_3'] = 'left';
        $report->addTAble($result, '', '', 'table color-table info-table', [], $aligments);

        $html_reporte = $report->getReport();

        return $html_reporte;
    }

    public function cambiar_estado($idbitacora)
    {
        $security      = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual = mysql::getvalue("SELECT estado FROM bitacora WHERE idbitacora = '$idbitacora' ");

        $DATOS['idbitacora'] = $idbitacora;
        $DATOS['estado']     = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $llaves              = ['idbitacora'];
        $resultado           = table::update_record($DATOS, $llaves);

        return mysql::getvalue("SELECT estado FROM bitacora WHERE bitacora = '$bitacora' ");
    }
}
