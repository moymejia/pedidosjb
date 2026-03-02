<?php
require_once 'security.php';
require_once 'mysql.php';
class opcion
{

    public function __construct($PARAMETROS = [])
    {
        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'cargar_opcion') {
                if (isset($PARAMETROS['idopcion_actual']) && $PARAMETROS['idopcion_actual'] != '') {
                    echo "|correcto|" . self::cargar_opcion($PARAMETROS['idopcion_actual']);
                } else {
                    echo "|error|Datos incompletos|";
                }
            }

        }
    }

    public function cargar_opcion($idopcion)
    {
        $mysql    = new mysql(prefijo . '_seguridad');
        $security = new security();

        $idaccion = $mysql->getvalue("SELECT idaccion FROM accion WHERE idopcion = $idopcion AND indOpcion = 'SI' AND estado = 'ACTIVO' ", "idaccion");
        $security->validar_permiso_accion($idaccion);
        $security->registrar_bitacora($idaccion, $idaccion);

        $entity_name = $mysql->getvalue("SELECT entity FROM opcion WHERE idopcion = $idopcion ", "entity");
        $funcion     = $mysql->getvalue("SELECT funcion FROM opcion WHERE idopcion = $idopcion ", "funcion");
        require_once '../entities/' . $entity_name . '.php';
        $entity = new $entity_name();

        return $entity->$funcion($_REQUEST);
    }
}
