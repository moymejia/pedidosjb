<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/set_talla.php';

class set_talla_detalle extends table
{
    use utils;
    private $idset_talla_detalle;
    private $ACCIONES  = [];
    public $last_error = '';

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'set_talla_detalle');

        $this->ACCIONES['cargar_talla']  = 198;
        $this->ACCIONES['agregar_talla'] = 220;
        $this->ACCIONES['retirar_talla'] = 221;

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'cargar_talla') {
                if (table::validate_parameter_existence(['idset_talla'], $PARAMETROS, false)) {
                    if ($resultado = $this->cargar_talla($PARAMETROS['idset_talla'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error('Parametros insuficientes');
                }
            }

            if ($PARAMETROS['operacion'] == 'agregar_talla') {
                if (table::validate_parameter_existence(['idset_talla', 'idtalla'], $PARAMETROS, false)) {
                    if ($resultado = $this->agregar_talla($PARAMETROS['idset_talla'], $PARAMETROS['idtalla'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("parametros insuficientes");
                }
            }

            if ($PARAMETROS['operacion'] == 'retirar_talla') {
                if (table::validate_parameter_existence(['idset_talla', 'idtalla'], $PARAMETROS, false)) {
                    if ($resultado = $this->retirar_talla($PARAMETROS['idset_talla'], $PARAMETROS['idtalla'])) {
                        self::end_success();
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("parametros insuficientes");
                }
            }
        }
    }

    public function cargar_talla($idset_talla)
    {

        $tallas_actuales = '';
        $SQL = "SELECT idtalla, numero FROM talla WHERE estado = 'ACTIVO' AND idtalla NOT IN (SELECT idtalla FROM set_talla_detalle WHERE idset_talla = $idset_talla)
            ORDER BY CAST(numero AS UNSIGNED) ASC";

        $RES = mysql::getresult($SQL);

        while ($ROW = mysql::getrowresult($RES)) {
            $idtalla = $ROW['idtalla'];
            $numero  = $ROW['numero'];

            $tallas_actuales .= "
                <div class=\"card m-b-10\">
                    <div class=\"card-header\" style=\"background:var(--icons-color); color: white;\"onclick=\"
                        callback_retirar_talla = function(){
                            notify_success('Talla retirada correctamente.');
                            delete callback_download;
                            download_div_content('idset_talla','set_talla_detalle','cargar_talla','talla');
                        }
                        element('idtalla').value = $idtalla;
                        upload_action('idset_talla,idtalla','set_talla_detalle','retirar_talla',callback_retirar_talla);
                    \">
                        <span style='text-transform:uppercase;font-weight:bold;'>Talla: $numero</span>
                        <small>quitar</small>
                    </div>
                </div>
                ";
        }

        $tallas_desponibles = '';
        $SQL = "SELECT idtalla, numero FROM talla WHERE estado = 'ACTIVO' AND idtalla NOT IN (SELECT idtalla FROM set_talla_detalle WHERE idset_talla = $idset_talla) ORDER BY CAST(numero AS UNSIGNED) ASC";

        $RES = mysql::getresult($SQL);

        while ($ROW = mysql::getrowresult($RES)) {
            $idtalla = $ROW['idtalla'];
            $numero  = $ROW['numero'];

            $tallas_desponibles .= "
            <div class=\"card\">
                <div class=\"card-header\" style=\"background:var(--icons-color); color: white;\"onclick=\"
                    callback_agregar_talla = function(){
                        notify_success('Talla agregada correctamente');
                        delete callback_download;
                        download_div_content('idset_talla','set_talla_detalle','cargar_talla','talla');
                    }
                    element('idtalla').value = $idtalla;
                    upload_action('idset_talla,idtalla','set_talla_detalle','agregar_talla',callback_agregar_talla);
                \">Talla: $numero</div>
            </div>
            ";
        }

        $html  = "
            <div class=\"form-group m-b-40 col-md-6\" id=\"tallas_actuales\">
                TALLAS ASIGNADAS <br>
                <small>clic en el encabezado para retirar</small>
                $tallas_actuales
            </div>
            <div class=\"form-group m-b-40 col-md-6\" id=\"tallas_desponibles\">
                TALLAS DISPONIBLES <br>
                <small>clic para agregar</small>
                <hr>
                $tallas_desponibles
            </div>
            ";

        return $html;
    }

    public function agregar_talla($idset_talla, $idtalla)
    {
        $idset_talla = table::sanitize_var($idset_talla);
        $idtalla     = table::sanitize_var($idtalla);

        $security = new security($this->ACCIONES['agregar_talla']);

        $set_talla = new set_talla();

        $estado_actual = $set_talla->estado($idset_talla);

        if ($estado_actual == 'INACTIVO' || $estado_actual == 'PROTEGIDO') {
            $this->last_error = "El set de tallas se encuentra en estado $estado_actual y no puede modificarse";
            utils::report_error(validation_error, "Modificar set de tallas", $this->last_error);

            return false;
        }

        if (mysql::exists('set_talla_detalle', "idset_talla = $idset_talla AND idtalla = $idtalla")) {
            return "Talla agregada previamente";
        }

        $DATOS = [
            'idset_talla'      => $idset_talla,
            'idtalla'          => $idtalla,
            'estado'           => 'ACTIVO',
            'usuario_creacion' => $security->get_actual_user(),
        ];

        if (table::insert_record($DATOS)) {
            $security->registrar_bitacora($this->ACCIONES['agregar_talla'], $idset_talla, $idtalla);

            return "Talla agregada correctamente";
        } else {
            $this->last_error = "Error al guardar el registro";
            return false;
        }
    }

    public function retirar_talla($idset_talla, $idtalla)
    {
        $idset_talla = table::sanitize_var($idset_talla);
        $idtalla     = table::sanitize_var($idtalla);

        $security  = new security($this->ACCIONES['retirar_talla']);
        $SET_TALLA = new set_talla();

        $estado_actual = $SET_TALLA->estado($idset_talla);

        if ($estado_actual == 'INACTIVO' || $estado_actual == 'PROTEGIDO') {
            $this->last_error = "El set de tallas se encuentra en estado $estado_actual y no puede modificarse";
            utils::report_error(validation_error, "Modificar set de tallas", $this->last_error);

            return false;
        }

        if (! mysql::exists('set_talla_detalle', "idset_talla = $idset_talla AND idtalla = $idtalla")) {
            return "Talla retirada previamente";
        }

        $DATA = [
            'idset_talla' => $idset_talla,
            'idtalla'     => $idtalla,
        ];

        if ($this->delete_record($DATA)) {
            $security->registrar_bitacora($this->ACCIONES['retirar_talla'], $idset_talla, $idtalla);

            return "Talla retirada correctamente";

        } else {
            $this->last_error = "Error al eliminar el registro";
            return false;
        }
    }

    public function get_tallas($idset_talla)
    {
        $sql = mysql::getresult("SELECT idtalla FROM set_talla_detalle WHERE idset_talla = '$idset_talla'");

        $tallas = [];

        while($row = mysql::getrowresult($sql)){
            $tallas[] = $row['idtalla'];
        }

        return $tallas;
    }

    public function get_set_talla($talla_desde,$talla_hasta) 
    {   
        return mysql::getvalue("SELECT idset_talla FROM view_set_talla_detalle GROUP BY idset_talla
                HAVING 
                    MIN(numero) = $talla_desde
                    AND MAX(numero) = $talla_hasta
                    AND COUNT(numero) = ($talla_hasta - $talla_desde + 1);");
    }

    public function guardar_tallas($idset_talla,$talla_desde,$talla_hasta)
    {   
        $TALLA = new talla();
        for($i = $talla_desde; $i <= $talla_hasta; $i++){
            $idtalla = $TALLA->get_idtalla($i);

            if(!$idtalla){
                $this->last_error = $TALLA->last_error;

                return false;
            }

            if(!$this->agregar_talla($idset_talla,$idtalla)){

                return false;
            }
        }
        return true;
    }
}