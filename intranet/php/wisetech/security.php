<?php
date_default_timezone_set('America/Guatemala');
require_once 'mysql.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    die();
}
//cargar conexion a base de datos y funciones basicas.
class security extends mysql
{
    private $user;
    private $idrol;

    public function __construct($idaccion = null)
    { //verificar opcion
        parent::__construct(); //incia conexion de base de datos
        $this->user  = $_SESSION['usuario'];
        $this->idrol = mysql::getvalue("SELECT idrol FROM usuario WHERE usuario = '" . $this->user . "' and estado = 'ACTIVO' ", 'idrol');
        if (isset($idaccion)) {
            $this->validar_permiso_accion($idaccion);
        }
//    valdia opcion inicial
    }

    /**
     * @param int $idaccion accion que sera validada.
     * @param bool $soft default false, termina ejecucion del codigo si no tiene permiso, true paraindicar que solo devuelva resultado y continue.
     * Verifica si el usuario actual teien permisos para la accion indicada. si no tiene permiso, rompe la ejecucion del proceso.
     */
    public function validar_permiso_accion($idaccion, $soft = false)
    { //permite validar el permiso para el usuario actual
        if (isset($idaccion)) {
            if (!$this->rol_has_permission($this->idrol, $idaccion)) {
                utils::report_error(auth_error, "accion:$idaccion", 'Permiso especifico no asignado');
                if (!$soft) {
                    die("|error|Sin permisos para realizar la accion solicitada|");
                } else {
                    return false;
                }
            }
        } else {
            die("|error|error de inicio de operacion|");
        }

        return true;
    }

    /**
     * @param int $idaccion accion que sera registrada
     * @param mixed $referencia1 Ogligatoria, referncia principal de la accion
     * @param mixed    $referencia2 Opcional, segunda referencia de la operacion.
     * @param mixed    $referencia3 Opcional, tercera referencia de la operacion.
     * registra un valor en la bitacora
     */
    public function registrar_bitacora($idaccion, $referencia1, $referencia2 = null, $referencia3 = null)
    { //registra bitacora.
        $user        = $this->user;
        $referencia2 = ($referencia2 == null) ? 'null' : "'$referencia2'";
        $referencia3 = ($referencia3 == null) ? 'null' : "'$referencia3'";
        mysql::put("INSERT INTO bitacora (idaccion, usuario, fechahora, referencia1, referencia2, referencia3) VALUES ('$idaccion', '$user', now(), '$referencia1', $referencia2, $referencia3 ) ");
    }

    private function rol_has_permission($idrol, $accion)
    {
        $exists = mysql::getvalue("SELECT count(1) existe FROM rol_accion WHERE idaccion = '$accion' and idrol = '$idrol' ", "existe");
        if ($exists > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function at_least_one_permission($array)
    {
        foreach ($array as $accion) {
            if ($this->rol_has_permission($this->idrol, $accion)) {
                return true;
            }

        }
        utils::report_error(auth_error, $array, 'Ningun permiso de lista asignado.');

        return false;
    }

    public function get_actual_user()
    {
        return $this->user;
    }

    //TODO: funcion para registrar reporte de permiso denegado.
}
