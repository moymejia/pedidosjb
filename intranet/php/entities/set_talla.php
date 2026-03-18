<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once '../entities/set_talla_detalle.php';

class set_talla extends table
{

    use utils;
    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'set_talla');
        $this->ACCIONES['crear'] = 32;

        if (isset($PARAMETROS['operacion'])) {

        }
    }

    public function get_idset_tallano($grupo)
    {   
        return mysql::getvalue("SELECT idset_talla FROM set_talla WHERE grupo = '$grupo'");
    }

    public function get_idset_talla($talla_desde,$talla_hasta) 
    {
        $SET_TALLA_DETALLE = new set_talla_detalle();

        $idset_talla_d = $SET_TALLA_DETALLE->get_set_talla($talla_desde,$talla_hasta);

        if(!empty($idset_talla_d) || $idset_talla_d = ''){

            return $idset_talla_d;
        }else{
            $grupo       = $talla_desde.'-'.$talla_hasta;
            $idset_talla = $this->guardar('',$grupo,''); 
            
            if(!$idset_talla){
                return false;
            }else{
                if(!$SET_TALLA_DETALLE->guardar_tallas($idset_talla,$talla_desde,$talla_hasta)){
                    return false;
                }
                echo "Set de talla creado $idset_talla_d";
                return $idset_talla;
            }
        }
    }

    public function guardar($idset_talla,$grupo,$descripcion)
    {
        if($idset_talla == '')
        {   
            $security = new security();

            $DATOS = [];
            $DATOS['grupo']            = $grupo;
            if($descripcion != ''){
                $DATOS['descripcion']  = $descripcion;
            }
            $DATOS['estado']           = 'ACTIVO';
            $DATOS['usuario_creacion'] = $security->get_actual_user();

            if(table::insert_record($DATOS)){
                $id = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear'], "guardar set talla",$id);

                return $id;
            }else{
                $this->last_error = "Error al guardar el set de tallas";
                utils::report_error(bd_error, $DATOS,$this->last_error);

                return false;
            }
        }
    }

    public function options_activos()
    {
        return mysql::getoptions("SELECT idset_talla id, grupo descripcion FROM set_talla WHERE estado = 'ACTIVO' ");
    }

}
