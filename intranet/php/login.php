<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'wisetech/mysql.php';
require_once 'wisetech/table.php';
$mysql = new mysql();
$table = new table('', '');

$usuario = $table->sanitize_var($_POST['usuario']);
$clave   = $table->sanitize_var($_POST['clave']);

// si hay una variable  token de sesion cargarla en la variable token
session_start();
$token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
echo "hola ";
$existe = $mysql->getvalue("SELECT count(1) existe FROM usuario WHERE usuario = '$usuario' and clave = sha2('$clave',256) and estado = 'ACTIVO' ", "existe");
//echo $existe;
if ($existe > 0) {
    $nombre                     = $mysql->getvalue("SELECT nombre, usuario FROM usuario WHERE usuario = '$usuario' ", "nombre");
    $_SESSION['usuario']        = $usuario;
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['token']          = $token;

    echo "|correcto|main.html.php|";
} else {
    session_destroy();
    echo "|error|Error de autenticacion, intente nuevamente. Si el problema persiste comuniquese con el administrador|";
}
