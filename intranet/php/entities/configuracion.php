<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/utils.php';
class configuracion extends table
{
    use utils;
    private $clave;
    private $last_error;
    private $ACCIONES = [];

    public function __construct($PARAMETROS = null)
    {
        parent::__construct(prefijo . '_seguridad', 'configuracion');

        $this->ACCIONES['modificar'] = 19;

        if (isset($PARAMETROS['operacion'])) {

            if ($PARAMETROS['operacion'] == 'guardar') {
                if ($resultado = self::guardar_configuracion($PARAMETROS)) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
            //
            if ($PARAMETROS['operacion'] == 'cargar_imagen') {
                if (table::validate_parameter_existence(['clave'], $PARAMETROS, false)) {
                    if ($resultado = $this->cargar_imagen($PARAMETROS['clave'])) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("No hay  clave seleccionado");
                }
            }
            //
        }
    }

    public function cargar_opcion()
    {
        $DATA                  = [];
        $CONFIGURACIONES       = mysql::getresult("SELECT clave, valor, comentario FROM configuracion ");
        $tabla_configuraciones = '';
        while ($row = mysql::getrowresult($CONFIGURACIONES)) {
            $clave      = $row['clave'];
            $valor      = str_replace(["\r\n", "\r", "\n"], '[sl]', $row['valor']);
            $comentario = $row['comentario'];
            //$row_data = getrowmysql("SELECT * FROM area WHERE idarea = '$idarea' ");
            $row_data = $row;
            $str_data = "";
            foreach ($row_data as $key => $value) {
                if ($value == null) {
                    $value = "";
                }

                $sin_saltos = str_replace(["\r\n", "\r", "\n"], '[sl]', $value);
                $str_data .= $key . "=" . $sin_saltos . "&";
                //$str_data  .= $key."=".$value."&";
            }
            $boton_editar = "<button  class=\"btn btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                editar_registro('$str_data',this.parentNode.parentNode);
                goTop();
                showElements('formulario_registro');
                element('miniatura_clave').src = '../img/prdMin_{$row['clave']}.jpg?x='+Date.now();
                \"><span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar</button>
                ";
            $tabla_configuraciones .= "<tr>
                <td>$boton_editar</td>
                <td>$clave</td>
				<td>$valor</td>
				<td>$comentario</td>
			</tr>";
        }
        $DATA['tabla_configuraciones'] = $tabla_configuraciones;

        $html = new html('configuracion', $DATA);

        return $html->get_html();
    }

    /**
     * @param array $PARAMETROS
     * si el configuracion existe, lo modifica, de lo contraro crea uno nuevo
     */
    public function guardar_configuracion($PARAMETROS)
    {
        $parametros_necesarios = ["clave", "valor", "comentario"];
        $clave = $PARAMETROS['clave'];

        if($clave == '' || empty($clave)){
            $this->last_error = "No se permite creacion de nuevos registros.";
            $this->report_error(validation_error, "Validación configuracion", $this->last_error);

            return false;
        }

        if (!table::validate_parameter_existence($parametros_necesarios, $PARAMETROS)) {
            self::end_error("Datos incompletos.");
        }

        $PARAMETROS          = table::sanitize_array($PARAMETROS);
        $PARAMETROS['valor'] = str_replace(["\r\n", "\r", "\n"], '[sl]', $PARAMETROS['valor']);
        if (mysql::exists('configuracion', " clave = '{$PARAMETROS['clave']}' ")) { //es configuracion nuevo
            $security           = new security($this->ACCIONES['modificar']); //modificar registro de configuracion
            $valores_necesarios = ["clave", "valor"];
            $DATOS              = table::create_subarray($valores_necesarios, $PARAMETROS);
            $llaves             = ["clave"];
            if ($resultado = table::update_record($DATOS, $llaves)) {
                $security->registrar_bitacora($this->ACCIONES['modificar'], $llaves);

                return "editado";
            } else {
                $this->last_error = "Error al modificar el registro";

                return false;
            }
        }
    }

    private function cargar_imagen($clave)
    {
        if ($_FILES['file_uploaded']['type'] != 'image/jpeg') {
            $this->last_error = "Tipo de archivo no permitido. Debe cargar imagenes en formato JPG";

            return false;
        }
        $destino   = '../../img/';
        $original  = $destino . 'prdB_' . $clave . '.jpg';
        $miniatura = $destino . 'prdMin_' . $clave . '.jpg';

        if(!move_uploaded_file($_FILES['file_uploaded']['tmp_name'], $original)){
            $this->last_error = "No se pudo guardar el archivo en $original";
            $this->report_error(validation_error, "Validación configuracion imagen", $this->last_error);

            return false;
        }

        $image = imagecreatefromjpeg($original);
        imagejpeg($image, $miniatura, 25);

        return "formato correcto";
    }

    public function obtener_valor($clave)
    {
        $result = mysql::getresult("SELECT valor FROM configuracion WHERE clave = '$clave' LIMIT 1");
        if ($row = mysql::getrowresult($result)) {
            return $row['valor'];
        } else {
            $this->last_error = "Clave no encontrada";
            utils::report_error(validation_error, $clave, 'Clave no encontrada');

            return false;
        }
    }

    public function get_datos_configuracion($clave){
        return mysql::getvalue("SELECT valor FROM configuracion WHERE clave = '$clave' ");
    }
}
