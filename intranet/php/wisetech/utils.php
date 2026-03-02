<?php
define('login_error', 1);
define('auth_error', 2);
define('bd_error', 3);
define('validation_error', 4);

$path = getcwd();
if (strpos($path, 'entities')) {
    require_once '../wisetech/table.php';
} else if (strpos($path, 'wisetech')) {
    require_once 'table.php';
} else {
    require_once 'wisetech/table.php';
}
$cantidad_errores = 0;
trait utils
{
    /**
     * @param string $return_text texto que sera devuelto al cliente  (navegador)
     * Termina la ejecucion del script, indicando que todo fue correcot y agregando el mensaje recibido.
     */
    public static function end_success($return_text = '')
    { //Finalizar notificando al navegador que la operacion se realizó correctamente.
        die("|correcto|" . $return_text);
    }

    public static function end_error($return_text = '')
    { //Finalizar notificando al navegador que la operacion  no pudo finalizarse
        die("|error|" . $return_text);
    }

    /**
     * @param integer $error_type constans: login_error, auth_error, bd_error, validation_error.
     * @param string||array $information array will be comverted to json string.
     * @param string $message to be registered as explanation of error.
     * @param bool $log_to_data_base if false, log directly to log file, default true
     */

    public static function report_error($error_type, $information, $message, $log_to_data_base = true)
    {
        global $cantidad_errores;
        $cantidad_errores++;
        if ($cantidad_errores > 3) {
            echo "exceso de errores, valide.";
            var_dump($information);
            var_dump($message);
            die();
        }
        $backtrace        = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 0);
        $origen['file_0'] = $backtrace[0]['file'];
        $origen['line_0'] = $backtrace[0]['line'];
        $origen['file_1'] = $backtrace[1]['file'];
        $origen['line_1'] = $backtrace[1]['line'];
        $origen['args_1'] = $backtrace[1]['args'];
        $origen           = json_encode($origen);

        if (gettype($information) == 'array') {
            $information = json_encode($information);
        }

        if ($log_to_data_base) {

            $usuario               = (isset($_SESSION['usuario'])) ? $_SESSION['usuario'] : 'No-conectado';
            $issue_table           = new table(prefijo . '_seguridad', 'error');
            $DATOS                 = [];
            $DATOS['idtipo_error'] = $error_type;
            $DATOS['mensaje']      = $message;
            $DATOS['datos']        = $information;
            $DATOS['origen']       = $origen;
            $DATOS['usuario']      = $usuario;

            if ($issue_table->insert_record($DATOS)) {
                return true;
            } else {
                ini_set("error_log", "logs/error_log_" . date('Y_m_d'));
                error_log("Error, sin registro en BD. tipo de error $error_type, origen: $origen, datos: $information, mensaje $message. ");
            }
        } else {
            ini_set("error_log", "logs/error_log_" . date('Y_m_d'));
            error_log("Error, sin registro en BD. tipo de error $error_type, origen: $origen, datos: $information, mensaje $message. ");
        }
    }
}
