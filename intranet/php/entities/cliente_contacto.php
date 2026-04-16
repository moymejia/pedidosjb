<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
require_once '../entities/usuario.php';
require_once '../entities/cliente.php';

class cliente_contacto extends table
{
    use utils;
    private $idcliente_contacto;
    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_pedidos', 'cliente_contacto');

        $this->ACCIONES['crear_contacto']      = "Crear_cliente_contacto";
        $this->ACCIONES['modificar_contacto']  = "Modificar_cliente_contacto";
        $this->ACCIONES['cambiar_estado']      = "Cambiar_estado_cliente_contacto";

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence(['idcliente','idtipo_contacto','nombre_contacto','estado_contacto'], $PARAMETROS, false)) {
                    if ($resultado = $this->guardar($PARAMETROS)) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'tabla_contactos') {
                if (table::validate_parameter_existence(['idcliente'], $PARAMETROS, false)) {
                    if ($resultado = $this->tabla_contactos($PARAMETROS['idcliente'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'cambiar_estado') {
                if (table::validate_parameter_existence(['idcliente_contacto'], $PARAMETROS, false)) {
                    self::end_success($this->cambiar_estado($PARAMETROS['idcliente_contacto']));
                } else {
                    self::end_error("Cliente no encontrado");
                }
            }
        }
    }

    public function tabla_contactos($idcliente)
    {
        $result = mysql::getresult("SELECT idcliente_contacto, idcliente, idtipo_contacto, nombre_contacto, telefono_contacto, correo_contacto, estado_contacto, 
            observaciones_contacto, tipo_contacto 
            FROM view_cliente_contacto 
            WHERE idcliente = '$idcliente' ORDER BY nombre_contacto
        ");

        $tabla = '<table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
            <thead>
                <tr style="background-color: var(--datatable-color);">
                    <th>Acciones</th>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Observaciones</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla_todos">';

        $tiene_registros = false;

        while ($row = mysql::getrowresult($result)) {

            $tiene_registros = true;

            $idcliente_contacto = $row['idcliente_contacto'];
            $tipo_contacto      = $row['tipo_contacto'];
            $nombre             = $row['nombre_contacto'];
            $telefono           = $row['telefono_contacto'] ?? '';
            $correo             = $row['correo_contacto'] ?? '';
            $estado             = $row['estado_contacto'];
            $observaciones      = $row['observaciones_contacto'];

            $str_data = "";
            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $boton_editar = "
                <button 
                    class=\"btn btn-sm btn-primary waves-effect waves-light\" 
                    type=\"button\" 
                    onclick=\"
                        clearElements('formulario_registro');
                        editar_registro('$str_data',this.parentNode.parentNode);
                        goTop();
                        showElements('botones_edicion_contacto');
                    \"
                >
                    <span class=\"btn-label\">
                        <i class=\"far fa-edit\"></i>
                    </span>
                    Editar
                </button>
            ";

            $tabla .= "<tr>
                    <td>$boton_editar</td>
                    <td>$tipo_contacto</td>
                    <td>$nombre</td>
                    <td>$telefono</td>
                    <td>$correo</td>
                    <td>$observaciones</td>
                    <td style='text-align:center;'>$estado</td>
                </tr>";
        }

        if (!$tiene_registros) {
            $tabla .= "<tr>
                    <td colspan='7' style='text-align:center;'>Sin registros</td>
                </tr>";
        }

        $tabla .= "</tbody></table>";

        return $tabla;
    }

    public function guardar($PARAMETROS)
    {   
        $CLIENTE = new cliente();
        $estado_actual_cliente = $CLIENTE->estado($PARAMETROS['idcliente']);

        if ($estado_actual_cliente == 'INACTIVO') {
            $this->last_error = 'Cliente en estado INACTIVO, no puede modificarse';
            $this->report_error(validation_error, "Modificar cliente", $this->last_error);
            return false;
        }

        if ($PARAMETROS['idcliente_contacto'] == '') {

            $security = new security($this->ACCIONES['crear_contacto']);
            $usuario  = $security->get_actual_user();

            $DATOS = [];
            $DATOS['idcliente']          = $PARAMETROS['idcliente'];
            $DATOS['idtipo_contacto']    = $PARAMETROS['idtipo_contacto'];
            $DATOS['nombre']             = $PARAMETROS['nombre_contacto'];
            $DATOS['telefono']           = $PARAMETROS['telefono_contacto'];
            $DATOS['correo']             = $PARAMETROS['correo_contacto'];
            $DATOS['observaciones']      = $PARAMETROS['observaciones_contacto'];
            $DATOS['usuario_creacion']   = $usuario;
            $DATOS['estado']             = 'ACTIVO';

            if ($resultado = table::insert_record($DATOS)) {
                $referencia = mysql::last_id();
                $security->registrar_bitacora($this->ACCIONES['crear_contacto'],$resultado,$referencia);

                return 'nuevo';

            } else {
                $this->last_error = "Error al guardar el registro";
                utils::report_error(bd_error, $DATOS, $this->last_error);
                return false;
            }

        } else {

            $security = new security($this->ACCIONES['modificar_contacto']);
            $usuario  = $security->get_actual_user();

            $DATOS = [];
            $DATOS['idcliente_contacto']    = $PARAMETROS['idcliente_contacto'];
            $DATOS['idcliente']             = $PARAMETROS['idcliente'];
            $DATOS['idtipo_contacto']       = $PARAMETROS['idtipo_contacto'];
            $DATOS['nombre']                = $PARAMETROS['nombre_contacto'];
            $DATOS['telefono']              = $PARAMETROS['telefono_contacto'];
            $DATOS['correo']                = $PARAMETROS['correo_contacto'];
            $DATOS['estado']                = $PARAMETROS['estado_contacto'];
            $DATOS['observaciones']         = $PARAMETROS['observaciones_contacto'];
            $DATOS['fecha_modificacion']    = date('Y-m-d H:i:s');
            $DATOS['usuario_modificacion']  = $usuario;

            $llaves = ['idcliente_contacto'];

            if ($resultado = table::update_record($DATOS, $llaves)) {
                $security->registrar_bitacora($this->ACCIONES['modificar_contacto'],$DATOS['idcliente_contacto'],$DATOS['nombre']);
                return 'editado';

            } else {
                $this->last_error = "Error al actualizar el registro";
                utils::report_error(bd_error, $DATOS, $this->last_error);
                return false;
            }
        }
    }

    private function cambiar_estado($idcliente_contacto)
    {
        $security = new security($this->ACCIONES['cambiar_estado']);
        $estado_actual = $this->estado($idcliente_contacto);

        if ($estado_actual == 'PROTEGIDO') {
            $this->last_error = "No se puede cambiar el estado. registro protegido";
            return false;
        }

        $nuevo_estado = ($estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
        $DATOS = [];
        $DATOS['idcliente_contacto'] = $idcliente_contacto;
        $DATOS['estado'] = $nuevo_estado;

        $llaves = ['idcliente_contacto'];

        if ($resultado = table::update_record($DATOS, $llaves)) {
            $security->registrar_bitacora($this->ACCIONES['cambiar_estado'],$idcliente_contacto,$nuevo_estado);

            return $nuevo_estado;

        } else {
            $this->last_error = "Error al cambiar el estado.";
            utils::report_error(bd_error, $idcliente_contacto, $this->last_error);
            return false;
        }
    }

    private function estado($idcliente_contacto)
    {
        return mysql::getvalue("SELECT estado FROM cliente_contacto WHERE idcliente_contacto = '$idcliente_contacto'");
    }

}
