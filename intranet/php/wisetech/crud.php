<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once 'utils.php';
require_once 'mysql.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IDROLCONSTANTE') && isset($_SESSION['usuario'])) {
    $usuario_constante = $_SESSION['usuario'];
    $_MYSQL            = new mysql(prefijo . '_seguridad');
    $idrol_constante   = $_MYSQL->getvalue("SELECT idrol FROM usuario WHERE usuario = '" . $usuario_constante . "' and estado = 'ACTIVO' ", 'idrol');
    define('IDROLCONSTANTE', $idrol_constante);
}

$table = $_POST['table'];
switch ($table) {
    case 'opcion':
        require_once $table . ".php";
        break;
    case 'datatables':
        require_once '../wisetech/' . $table . ".php";
        break;
    case 'ping':
        die("|correcto|" . ping());
    default:
        require_once '../entities/' . $table . ".php";
        break;
}
$clase = new $table($_REQUEST);

// crear funcion ping si hay sesion abierta devuelve true, de lo contrario false
function ping()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['usuario'])) {
        return 1;
    } else {
        return 0;
    }
}
