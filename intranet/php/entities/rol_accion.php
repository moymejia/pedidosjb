<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
class rol_accion extends table
{
    use utils;
    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_seguridad', 'rol_accion');

        $this->ACCIONES['cargar_permisos'] = 9;
        $this->ACCIONES['agregar_permiso'] = 10;
        $this->ACCIONES['retirar_permiso'] = 11;
        $this->ACCIONES['agregar_opcion']  = 14;
        $this->ACCIONES['retirar_opcion']  = 15;

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'cargar_permisos') {
                if (table::validate_parameter_existence(['idrol'], $PARAMETROS, false)) {
                    if ($resultado = self::cargar_permisos($PARAMETROS['idrol'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("parametros insuficientes");
                }
            }

            if ($PARAMETROS['operacion'] == 'agregar_permiso') {
                if (table::validate_parameter_existence(['idrol', 'idaccion'], $PARAMETROS, false)) {
                    if ($resultado = self::agregar_permiso($PARAMETROS['idrol'], $PARAMETROS['idaccion'])) {
                        self::end_success();
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("parametros insuficientes");
                }
            }

            if ($PARAMETROS['operacion'] == 'retirar_permiso') {
                if (table::validate_parameter_existence(['idrol', 'idaccion'], $PARAMETROS, false)) {
                    if ($resultado = self::retirar_permiso($PARAMETROS['idrol'], $PARAMETROS['idaccion'])) {
                        self::end_success();
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("parametros insuficientes");
                }
            }

            if ($PARAMETROS['operacion'] == 'agregar_opcion') {
                if (table::validate_parameter_existence(['idrol', 'idopcion'], $PARAMETROS, false)) {
                    if ($resultado = self::agregar_opcion($PARAMETROS['idrol'], $PARAMETROS['idopcion'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("parametros insuficientes");
                }
            }

            if ($PARAMETROS['operacion'] == 'retirar_opcion') {
                if (table::validate_parameter_existence(['idrol', 'idopcion'], $PARAMETROS, false)) {
                    if ($resultado = self::retirar_opcion($PARAMETROS['idrol'], $PARAMETROS['idopcion'])) {
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

    public function cargar_opcion()
    {
        $DATA        = [];
        $result      = mysql::getresult("SELECT idrol, nombre FROM rol WHERE estado = 'ACTIVO' ");
        $tabla_roles = '';
        while ($row = mysql::getrowresult($result)) {
            $idrol  = $row['idrol'];
            $nombre = $row['nombre'];
            //$row_data = getrowmysql("SELECT * FROM area WHERE idarea = '$idarea' ");
            $boton_editar = "<button  class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                objeto('idrol').value = $idrol;
                objeto('rol').value = '$nombre';
                move_label('rol');
                delete callback_download;
				download_div_content('idrol','rol_accion','cargar_permisos','permisos');
				goTop();
            \"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>";
            $tabla_roles .= "<tr>
				<td>$boton_editar</td>
				<td>$idrol</td>
				<td>$nombre</td>
			</tr>";
        }
        $DATA['tabla_roles'] = $tabla_roles;

        $html = new html('rol_accion', $DATA);

        return $html->get_html();
    }

    public function cargar_permisos($idrol)
    {
        $html = "
			<div class=\"form-group m-b-40 col-md-6\" id=\"permisos_actuales\">
				OPCIONES ACTUALES <br>
				<small>clic en el encabezado para retirar permiso</small><br>
				<small>
					<span style='background:lightgreen'>Accion en verde</span>= disponible,<br>
					<span style='background:lightcoral'>Accion en rojo</span>= no disponible. clic para cambiar
				</small>
				[permisos_actuales]
			</div>
			<div class=\"form-group m-b-40 col-md-6\" id=\"permisos_disponibles\">
				OPCIONES DISPONIBLES <br>
				<small>clic en para agregar permiso</small>
				<hr>
				[permisos_disponibles]
			</div>
		";
        $permisos_actuales = '';
        $OPCIONES          = mysql::getresult("SELECT idopcion, opcion FROM view_permisos  WHERE idrol = $idrol  GROUP BY idopcion ORDER BY orden_menu, orden_opcion");
        while ($OPCION = mysql::getrowresult($OPCIONES)) {
            $idopcion = $OPCION['idopcion'];
            $opcion   = $OPCION['opcion'];
            $permisos_actuales .= "<div class=\"card m-b-10\">
                <div class=\"card-header\" style=\"background:var(--icons-color); color:white;\">
                    
                    <span style='display:inline-block; cursor:pointer; border:solid thin lightgray; padding:5px;' 
                        title='Click para quitar' 
                        onclick=\"
                            delete callback_upload;
                            callback_upload = function(){
                                notify_success('Permiso retirado correctamente');
                                delete callback_download;
                                download_div_content('idrol','rol_accion','cargar_permisos','permisos');
                            }
                            element('idopcion').value = $idopcion
                            upload_action('idrol,idopcion','rol_accion','retirar_opcion');
                        \">

                        <span style='text-transform:uppercase;font-weight:bold;'>$opcion</span>
                        <small>quitar</small>

                    </span>

                    <span style='display:inline-block; cursor:pointer; border:solid thin lightgray; padding:5px;' 
                        class='pull-right'
                        onclick=\"
                            if(element('acciones_$idopcion').style.display == '') {
                                hideElement('acciones_$idopcion')
                            } else {
                                showElement('acciones_$idopcion')
                            }
                        \">
                        Detalles
                    </span>

                </div>

                <div class=\"card-body collapse show\" id=\"acciones_$idopcion\" style='display:none'>
                    [acciones]
                </div>

            </div>";

            $acciones = '';
            $ACCIONES = mysql::getresult("SELECT idaccion, accion, 'activo' estado FROM view_permisos WHERE idopcion = $idopcion AND idrol = $idrol AND indOpcion = 'NO'
			UNION
			SELECT idaccion, a.nombre accion, 'inactivo' estado FROM accion a WHERE a.estado = 'ACTIVO' AND a.idopcion = $idopcion AND a.idaccion NOT IN (SELECT idaccion FROM view_permisos WHERE idrol = $idrol ) ");
            while ($ACCION = mysql::getrowresult($ACCIONES)) {
                $idaccion     = $ACCION['idaccion'];
                $accion       = $ACCION['accion'];
                $estado       = $ACCION['estado'];
                $fondo_actual = ($estado == 'activo') ? 'lightgreen' : 'lightcoral';
                $acciones .= "<p class=\"card-text\" id='accion_$idaccion' style='text-align:center;color:black;background:$fondo_actual;' onclick=\"
					delete callback_upload;
					callback_upload = function(){
						element('accion_$idaccion').style.background = (element('accion_$idaccion').style.background == 'lightgreen') ? 'lightcoral' : 'lightgreen';
						element('estado_accion_$idaccion').value = (element('estado_accion_$idaccion').value == 'activo') ? 'inactivo' : 'activo';
					}
					element('idaccion').value = $idaccion
					if(element('estado_accion_$idaccion').value == 'activo'){
						upload_action('idrol,idaccion','rol_accion','retirar_permiso');
					}else{
						upload_action('idrol,idaccion','rol_accion','agregar_permiso');
					}
				\">
					<input type='hidden' id='estado_accion_$idaccion' value='$estado' >
					$accion
				</p>";
            }
            $permisos_actuales = str_replace('[acciones]', $acciones, $permisos_actuales);
        }

        $permisos_disponibles = '';
        $OPCIONES             = mysql::getresult("SELECT idopcion, nombre opcion FROM opcion WHERE estado = 'ACTIVO' AND idopcion NOT IN (
			SELECT idopcion FROM view_permisos  WHERE idrol = $idrol  GROUP BY idopcion );");
        while ($OPCION = mysql::getrowresult($OPCIONES)) {
            $idopcion = $OPCION['idopcion'];
            $opcion   = $OPCION['opcion'];
            $permisos_disponibles .= "<div class=\"card\">
			<div class=\"card-header\" onclick=\"
				delete callback_upload;
				callback_upload = function(){
					notify_success('Permiso agregado correctamente');
					delete callback_download;
					download_div_content('idrol','rol_accion','cargar_permisos','permisos');
				}
				element('idopcion').value = $idopcion
				upload_action('idrol,idopcion','rol_accion','agregar_opcion');
			\">$opcion</div>
			</div>
			";
        }

        $html = str_replace('[permisos_actuales]', $permisos_actuales, $html);
        $html = str_replace('[permisos_disponibles]', $permisos_disponibles, $html);

        return $html;
    }

    public function agregar_permiso($idrol, $idaccion)
    {
        //sanitizar variables recibidas.
        $idrol    = table::sanitize_var($idrol);
        $idaccion = table::sanitize_var($idaccion);
        //verificar permisos
        $security = new security($this->ACCIONES['agregar_permiso']);
        //verifricar si la accion estaba registrada prveviamente.
        if (mysql::exists('rol_accion', " idaccion = $idaccion and idrol = $idrol ")) {
            return "permiso agregada previamente";
        }
        //insertar registro y registrar bitacora
        $DATOS['idrol']       = $idrol;
        $DATOS['idaccion']    = $idaccion;
        $DATOS['indFavorito'] = 'NO';
        if ($resultado = table::insert_record($DATOS)) {
            $security->registrar_bitacora($this->ACCIONES['agregar_permiso'], $idrol, $idaccion);

            return "Accion agregada correctamente";
        } else {
            $this->last_error = "Error al guardar el registro";

            return false;
        }
    }

    public function retirar_permiso($idrol, $idaccion)
    {
        //sanitizar variables recibidas.
        $idrol    = table::sanitize_var($idrol);
        $idaccion = table::sanitize_var($idaccion);
        //verificar permisos
        $security = new security($this->ACCIONES['retirar_permiso']);
        //verifricar si la accion estaba registrada prveviamente.
        if (!mysql::exists('rol_accion', " idaccion = $idaccion and idrol = $idrol ")) {
            return "permiso retirado previamente";
        }
        //eliminar registro y registrar bitacora
        $DATOS['idrol']    = $idrol;
        $DATOS['idaccion'] = $idaccion;
        if ($resultado = table::delete_record($DATOS)) {
            $security->registrar_bitacora($this->ACCIONES['retirar_permiso'], $idrol, $idaccion);

            return "accion retirada correctamente";
        } else {
            $this->last_error = "Error al eliminar el registro";

            return false;
        }
    }

    public function agregar_opcion($idrol, $idopcion)
    {
        //limpiar variables.
        $idrol    = table::sanitize_var($idrol);
        $idopcion = table::sanitize_var($idopcion);
        //verificar permisos
        $security = new security($this->ACCIONES['agregar_opcion']);
        //obtener accion principal de la opcion y validar si ya esta agregada
        $idaccion = mysql::getvalue("SELECT idaccion FROM accion WHERE idopcion = $idopcion AND indOpcion = 'SI'");
        if (mysql::exists('rol_accion', " idaccion = $idaccion and idrol = $idrol ")) {
            return "opcion agregada previamente";
        }
        //insertar registro
        $DATOS['idrol']       = $idrol;
        $DATOS['idaccion']    = $idaccion;
        $DATOS['indFavorito'] = 'NO';
        if ($resultado = table::insert_record($DATOS)) {
            $security->registrar_bitacora($this->ACCIONES['agregar_opcion'], $idrol, $idaccion);

            return "Opcion agregada correctamente";
        } else {
            $this->last_error = "Error al guardar el registro";

            return false;
        }
    }

    public function retirar_opcion($idrol, $idopcion)
    {
        //sanitizar variables
        $idrol    = table::sanitize_var($idrol);
        $idopcion = table::sanitize_var($idopcion);
        //verificar permisos
        $security = new security($this->ACCIONES['retirar_opcion']);
        //obtener accion principal de la opcion y validar si ya ha sido eliminada
        $idaccion = mysql::getvalue("SELECT idaccion FROM accion WHERE idopcion = $idopcion AND indOpcion = 'SI'");
        if (!mysql::exists('rol_accion', " idaccion = $idaccion AND idrol = $idrol ")) {
            return "opcion retirada previamente";
        }

        //eliminar todas las acciones vinculadas a la opcion indicada
        $DATOS['idrol']    = $idrol;
        $DATOS['idaccion'] = $idaccion;
        if (mysql::put("DELETE FROM rol_accion WHERE idrol = $idrol AND idaccion in (SELECT idaccion FROM accion WHERE idopcion = '$idopcion') ")) {
            $security->registrar_bitacora($this->ACCIONES['retirar_opcion'], $idrol, $idaccion);

            return "Opcion retirada correctamente";
        } else {
            $this->last_error = "Error al eliminar los registros";

            return false;
        }
    }
}
