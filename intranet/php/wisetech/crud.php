<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once 'utils.php';
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
    session_start();
    if (isset($_SESSION['usuario'])) {
        return 1;
    } else {
        return 0;
    }
}
