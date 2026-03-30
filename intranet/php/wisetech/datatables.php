<?php
require_once 'mysql.php';
require_once 'security.php';

    class   datatables extends mysql {
        public function __construct($PARAMETROS ) {
            if (isset($PARAMETROS['operacion'])) {
                switch ($PARAMETROS['operacion']) {
                    case 'guardar_estado_datatables':
                        if (isset($PARAMETROS['idtabla']) && isset($PARAMETROS['estadotabla'])) {
                            echo "|correcto|" . self::guardar_estado_datatables($PARAMETROS['idtabla'], $PARAMETROS['estadotabla']);
                        } else {
                            echo "|error|Datos incompletos|";
                        }
                        break;
                case 'cargar_estado_datatables':
                        echo "|correcto|" . self::cargar_estado_datatables(  );
                        break;
                    default:
                        echo "|error|Operacion no reconocida|";
                        break;
                }
            } else {
                echo "|error|Operacion no especificada|";
            }
        }
        public function cargar_estado_datatables() {
            $db = new mysql();
            $usuario = ( new security())->get_actual_user() ;
            $sql = "SELECT tabla, estado FROM legans_seguridad.datatables WHERE usuario = '$usuario' ";
            $result = $db->getresult($sql);
            $idtabla = '';
            while ($row = $db->getrowresult($result)) {
                $idtabla = $row['tabla'];
                $estado  = json_decode($row['estado'], true); // ← aquí

                $estados[$idtabla] = $estado;
            }

            return json_encode($estados);
        }
        public function guardar_estado_datatables($tabla, $estado) {
            $usuario = ( new security())->get_actual_user() ;
            $estado  = urldecode($estado); 
            $sql = "INSERT INTO pedidosjb_seguridad.datatables (usuario, tabla, estado)
            VALUES ('$usuario', '$tabla', '$estado')
            ON DUPLICATE KEY UPDATE estado = VALUES(estado)";
            $db = new mysql();

            return $db->getresult($sql);
        }
    }