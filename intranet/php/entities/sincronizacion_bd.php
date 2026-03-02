<?php
    require_once '../wisetech/table.php';
    require_once '../wisetech/security.php';
    require_once '../wisetech/html.php';
    require_once '../wisetech/utils.php';

    class sincronizacion_bd extends table
    {
        use utils;
        private $idsincronizacion_bd;
        private $last_error;
        private $path_sincronizacion = __DIR__ . "/../../../sql/";
        private $ACCIONES = [];

        public function __construct($PARAMETROS = null)
        {
            parent::__construct(prefijo . '_seguridad', 'sincronizacion_bd');
            $this->ACCIONES['sincronizacion_bd'] = 88;

            if (isset($PARAMETROS['operacion'])) {

            }
        }

        public function sicronizar_bd()
        {   
            $security = new security($this->ACCIONES['sincronizacion_bd']);
            
            if (!$security->validar_permiso_accion($this->ACCIONES['sincronizacion_bd'], true)) {
                $this->last_error = "Sin permisos para realizar la operacion.";
                echo $this->last_error;
                self::report_error(validation_error, $this->ACCIONES['sincronizacion_bd'], $this->last_error);
                return false;
            }

            if ($archivos = scandir($this->path_sincronizacion)) {
                $archivos_sql = [];

                foreach ($archivos as $archivo) {
                    $ruta = $this->path_sincronizacion . $archivo;

                    if(mysql::getvalue("SELECT COUNT(*) FROM sincronizacion_bd WHERE archivo = '$archivo'") > 0){
                        $this->last_error = "Error el archivo $archivo ya fue procesado.";
                        self::report_error(validation_error, $this->ACCIONES['sincronizacion_bd'], $this->last_error);

                        continue;
                    }

                    if (is_file($ruta) && pathinfo($archivo, PATHINFO_EXTENSION) === 'sql') {
                        $archivos_sql[] = [
                            'nombre' => $archivo,
                            'ruta'   => $ruta,
                            'fecha'  => filectime($ruta)
                        ];
                    }
                }

                usort($archivos_sql, function ($a, $b) {
                    return $a['fecha'] <=> $b['fecha'];
                });

                foreach ($archivos_sql as $file) {
                    $contenido = file_get_contents($file['ruta']);

                    $sentencias = array_filter(array_map('trim', explode(';', $contenido)));
                    $no_sentencias = 0;
                    foreach ($sentencias as $sql) {
                        $no_sentencias++;
                        if ($resultado = mysql::put($sql)) {
                            $DATOS = [];
                            $DATOS['archivo'] = $file['nombre'];
                            $DATOS['no_sentencia'] = $no_sentencias;

                            if(!table::insert_record($DATOS)){
                                $this->last_error = "Error en la insercion de registro de sincronizacion.";
                                self::report_error(bd_error, $DATOS, $this->last_error);

                                return false;
                            }
                        }else{
                            $this->last_error = "Error en la ejecucion de la sentencia $no_sentencias del archivo {$file['nombre']}";
                            echo $this->last_error;
                            self::report_error(bd_error, $no_sentencias, $this->last_error);

                            return false;
                        }
                    }
                }
            } else {
                $this->last_error = "Error al abrir el directorio.";
                echo $this->last_error;
                self::report_error(validation_error, $this->path_sincronizacion, $this->last_error);
                return false;
            }
        }

    }
    $idsincronizacion_bd = new sincronizacion_bd();
    $idsincronizacion_bd->sicronizar_bd();
?>