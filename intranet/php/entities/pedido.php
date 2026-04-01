<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
require_once('../entities/cliente.php');
require_once('../entities/marca.php');
require_once('../entities/temporada.php');
require_once('../entities/set_talla.php');
require_once('../entities/temporada.php');
require_once('../entities/transporte.php');
require_once('../entities/usuario.php');

class pedido extends table{

    use utils;

    private $last_error;
    private $ACCIONES = array();

    public function __construct($PARAMETROS = null){

        parent::__construct(prefijo . '_pedidos', 'pedido');

        $this->ACCIONES['crear']            = 27;
        $this->ACCIONES['eliminar']         = 28;
        $this->ACCIONES['cerrar_pedido']    = 29;
        $this->ACCIONES['imprimir_pedido']  = 33;

        if(isset($PARAMETROS['operacion'])){

            if ($PARAMETROS['operacion'] == 'guardar') {
                if (table::validate_parameter_existence([ 'nopedido','idcliente', 'idmarca', 'fecha_desde', 'fecha_hasta', 'idtemporada','idtransporte'], $PARAMETROS, false)) {

                    if ($resultado = $this->guardar($PARAMETROS)) {
                        self::end_success($resultado);
                    } else {
                        self::end_error($this->last_error);
                    }

                } else {
                    self::end_error("Faltan parámetros");
                }
            }

            if ($PARAMETROS['operacion'] == 'eliminar') {
                if (table::validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
                    if ($options = $this->eliminar($PARAMETROS['idpedido'])) {
                        self::end_success($options);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parámetros faltantes");
                }
            }

            if ($PARAMETROS['operacion'] == 'cerrar_pedido') {
                if (table::validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
                    if ($options = $this->cerrar_pedido($PARAMETROS['idpedido'])) {
                        self::end_success($options);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parámetros faltantes");
                }
            }

            if ($PARAMETROS['operacion'] == 'imprimir_pedido') {
                if (table::validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
                    if ($options = $this->imprimir_pedido($PARAMETROS['idpedido'])) {
                        self::end_success($options);
                    } else {
                        self::end_error($this->last_error);
                    }
                } else {
                    self::end_error("Parámetros faltantes");
                }
            }
        
        }
    }

    public function cargar_opcion()
    {
        $DATA = [];
        $DATA['clientes']       = (new cliente())->option_activas();
        $DATA['marcas']         = (new marca())->option_activas();
        $DATA['temporadas']     = (new temporada())->option_activos();
        $DATA['set_tallas']     = (new set_talla())->options_activos();
        $DATA['transportes']    = (new transporte())->option_activas();

        $result = mysql::getresult("SELECT idpedido, nopedido, idcliente, idtemporada, idmarca, cliente, temporada, marca, estado, 
            fecha_desde, fecha_hasta, observaciones_pedido, idtransporte, transporte, monto_descuento, email
            FROM view_pedidos 
            ORDER BY idpedido DESC");

        $tabla = '
        <table id="tabla_datos" class="display nowrap table table-hover table-bordered datatable" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Acciones</th>
                    <th>No. pedido</th>
                    <th>Cliente</th>
                    <th>Temporada</th>
                    <th>Marca</th>
                    <th>Fecha desde</th>
                    <th>Fecha hasta</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tabla_todos">';

        while ($row = mysql::getrowresult($result)) {
            $idpedido        = $row['idpedido'];
            $nopedido        = $row['nopedido'];
            $cliente         = $row['cliente'];
            $idcliente       = $row['idcliente'];
            $temporada       = $row['temporada'];
            $idtemporada     = $row['idtemporada'];
            $marca           = $row['marca'];
            $idmarca         = $row['idmarca'];
            $transporte      = $row['transporte'];
            $monto_descuento = $row['monto_descuento'];
            $estado          = $row['estado'];
            $fecha_desde     = date('Y-m-d', strtotime($row['fecha_desde']));
            $fecha_hasta     = date('Y-m-d', strtotime($row['fecha_hasta']));
            $observaciones_pedido  = date('Y-m-d', strtotime($row['observaciones_pedido']));

            $str_data = "";
            foreach ($row as $key => $value) {
                $str_data .= $key . "=" . $value . "&";
            }

            $estado_actual = $this->estado($idpedido);


            $boton_editar = "<button class=\"btn btn-sm btn-primary waves-effect waves-light\" type=\"button\" onclick=\"
                    editar_registro('$str_data',this.parentNode.parentNode);
                    showElements('detalles_del_pedido');
                    hideElements('lista_pedidos');
                    if('".$estado_actual."' == 'CERRADO'){
                        showElements('btn_imprimir');
                        hideElements('btn_cerrar_pedido');
                    }
                    disableElements('idcliente,idmarca,fecha_desde,fecha_hasta,idtemporada,observaciones_pedido,btn_limpiar_pedido,btn_guardar_pedido,idtransporte,monto_descuento,email,nopedido');
                    cargarDetallePedido();
                    goTop();\">
                    <span class=\"btn-label\"><i class=\"far fa-edit\"></i></span>Editar
                </button>";

            $tabla .= "
                <tr>
                    <td>$boton_editar</td>
                    <td>$nopedido</td>
                    <td>$cliente</td>
                    <td>$temporada</td>
                    <td style='text-align: center;'>$marca</td>
                    <td>$fecha_desde</td>
                    <td>$fecha_hasta</td>
                    <td style='text-align: center;'>$estado</td>
                </tr>";
        }

        $tabla .= "</tbody></table>";
        $DATA['tabla_pedidos'] = $tabla;
        $html = new html('pedido', $DATA);

        return $html->get_html();
    }

    public function guardar($PARAMETROS)
    {
        $security = new security($this->ACCIONES['crear']);
        $usuario  = $security->get_actual_user();

        if ($PARAMETROS['monto_descuento'] < 0 || $PARAMETROS['monto_descuento'] > 99) {
            $this->last_error = "El descuento debe estar entre 0 y 99%.";
            $this->report_error(validation_error, $usuario, $this->last_error);
            return false;
        }

        if (mysql::exists("pedido", "nopedido = '" . addslashes($PARAMETROS['nopedido']) . "'")) {
            $this->last_error = "El número de pedido ya existe";
            utils::report_error(validation_error,$PARAMETROS['nopedido'],$this->last_error);
            return false;
        }

        $DATOS = [];
        $DATOS['idcliente']             = $PARAMETROS['idcliente'];
        $DATOS['nopedido']              = $PARAMETROS['nopedido'];
        $DATOS['idmarca']               = $PARAMETROS['idmarca'];
        $DATOS['fecha_desde']           = $PARAMETROS['fecha_desde'];
        $DATOS['fecha_hasta']           = $PARAMETROS['fecha_hasta'];
        $DATOS['idtemporada']           = $PARAMETROS['idtemporada'];
        $DATOS['email']                 = $PARAMETROS['email'];
        $DATOS['observaciones_pedido']  = $PARAMETROS['observaciones_pedido'];
        $DATOS['idtransporte']          = $PARAMETROS['idtransporte'];
        $DATOS['monto_descuento']       = $PARAMETROS['monto_descuento'];
        $DATOS['estado']                = 'BORRADOR';
        $DATOS['fecha_creacion']        = date("Y-m-d H:i:s");
        $DATOS['usuario_creacion']      = $usuario;

        if ($resultado = table::insert_record($DATOS)) {
            $idpedido = mysql::last_id();
            $security->registrar_bitacora($this->ACCIONES['crear'], $resultado, $idpedido);

            return $idpedido;

        } else {
            $this->last_error = "Error al guardar el pedido";
            utils::report_error(bd_error, $DATOS, $this->last_error);

            return false;
        }

    }

    
    public function eliminar($idpedido)
    {
        $security = new security($this->ACCIONES['eliminar']);
        $usuario  = $security->get_actual_user();


        $estado_actual = $this->estado($idpedido);
        if($estado_actual != 'BORRADOR'){
            $this->last_error = 'No se puede eliminar un pedido diferente a estado BORRADOR.';
            $this->report_error(validation_error,$idpedido,$this->last_error);

            return false;
        }

        $DATOS = [];
        $DATOS['idpedido']     = $idpedido;

        if(table::delete_record($DATOS)){
            $security->registrar_bitacora($this->ACCIONES['eliminar'],$idpedido,$usuario);
            
            return true;
        }else{
            $this->last_error = "Error al eliminar el pedido.";
            $this->report_error(bd_error, $idpedido, $this->last_error);

            return false;
        }
    }

    public function cerrar_pedido($idpedido)
    {
        $security = new security($this->ACCIONES['cerrar_pedido']);
        $usuario  = $security->get_actual_user();

        $estado_actual = $this->estado($idpedido);

        if($estado_actual != 'BORRADOR'){
            $this->last_error = 'Solo se pueden cerrar pedidos en estado BORRADOR.';
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $result = mysql::getresult("SELECT SUM(cantidad) AS total_pares, SUM(subtotal) AS monto_total FROM pedido_detalle WHERE idpedido = $idpedido");

        if(!$result){
            $this->last_error = "Error al calcular totales del pedido.";
            $this->report_error(bd_error, $idpedido, $this->last_error);
            return false;
        }

        $row = mysql::getrowresult($result);

        $total_pares = $row['total_pares'] ? (int)$row['total_pares'] : 0;
        $monto_total = $row['monto_total'] ? (float)$row['monto_total'] : 0;

        if($total_pares <= 0){
            $this->last_error = "No se puede cerrar un pedido sin productos.";
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $monto_subtotal = $monto_total;
        $pedido = mysql::getrow("SELECT monto_descuento FROM pedido WHERE idpedido = $idpedido");
        $monto_descuento = $pedido && $pedido['monto_descuento'] ? (float)$pedido['monto_descuento'] : 0;

        $descuento_valor = ($monto_total * $monto_descuento) / 100;
        $monto_total = $monto_total - $descuento_valor;


        if($monto_total < 0){
            $monto_total = 0;
        }

        $DATOS = [];
        $DATOS['idpedido']             = $idpedido;
        $DATOS['estado']               = 'CERRADO';
        $DATOS['monto_subtotal']       = $monto_subtotal;
        $DATOS['total_pares']          = $total_pares;
        $DATOS['monto_total']          = $monto_total;
        $DATOS['fecha_modificacion']   = date('Y-m-d H:i:s');
        $DATOS['usuario_modificacion'] = $usuario;

        $llaves = ['idpedido'];

        if(table::update_record($DATOS, $llaves)){
            $security->registrar_bitacora($this->ACCIONES['cerrar_pedido'], $idpedido, "Pedido cerrado | Pares: $total_pares | Total: $monto_total | Descuento: $monto_descuento");
            return true;

        }else{
            $this->last_error = "Error al cerrar el pedido.";
            $this->report_error(bd_error, $DATOS, $this->last_error);
            return false;
        }
    }

    public function imprimir_pedido($idpedido)
    {
        $security = new security($this->ACCIONES['imprimir_pedido']);
        $usuario  = $security->get_actual_user();

        $usuarioObj = new usuario();
        $nombre_usuario = $usuarioObj->get_nombre($usuario);


        $PEDIDO = mysql::getrow("SELECT idpedido, idcliente, cliente, telefono, direccion, nit, establecimiento, marca, transporte, email, fecha_desde, fecha_hasta, observaciones_pedido, fecha_creacion, dias_credito, descripcion_marca
            FROM view_pedidos
            WHERE idpedido = '$idpedido'");

        if (!$PEDIDO) {
            $this->last_error = "No se encontró la información del pedido.";
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $MAX_TALLAS = 10;
        $sets_catalogo = [];
        $tallas_header_block = '';

        $sql_sets = mysql::getresult("SELECT DISTINCT vpd.idset_talla, COALESCE(vpd.grupo, '') AS grupo, COALESCE(vpd.set_descripcion, '') AS set_descripcion, vstd.orden, vstd.idtalla, vstd.talla
            FROM view_pedido_detalle vpd JOIN view_set_talla_detalle vstd ON vstd.idset_talla = vpd.idset_talla
            WHERE vpd.idpedido = '$idpedido'
            ORDER BY vpd.grupo ASC, vpd.set_descripcion ASC, vpd.idset_talla ASC, vstd.orden ASC");

        if (!$sql_sets) {
            $this->last_error = "Error al obtener los grupos de tallas del pedido.";
            $this->report_error(bd_error, $idpedido, $this->last_error);
            return false;
        }

        while ($row = mysql::getrowresult($sql_sets)) {
            $idset_talla = (int)$row['idset_talla'];

            if (!isset($sets_catalogo[$idset_talla])) {
                $sets_catalogo[$idset_talla] = [
                    'grupo' => trim((string)$row['grupo']),
                    'descripcion' => trim((string)$row['set_descripcion']),
                    'tallas' => []
                ];
            }

            $sets_catalogo[$idset_talla]['tallas'][] = [
                'idtalla' => (int)$row['idtalla'],
                'numero' => (string)$row['talla']
            ];
        }

        if (empty($sets_catalogo)) {
            $this->last_error = "El pedido no tiene grupos de tallas para imprimir.";
            $this->report_error(validation_error, $idpedido, $this->last_error);
            return false;
        }

        $rowspan_encabezado = count($sets_catalogo);

        $primer_header = true;

        foreach ($sets_catalogo as $set_info) {
            $grupo = htmlspecialchars($set_info['grupo'], ENT_QUOTES, 'UTF-8');
            $descripcion_set = htmlspecialchars($set_info['descripcion'], ENT_QUOTES, 'UTF-8');

            $tallas_header_block .= "<tr>";

            if ($primer_header) {
                $tallas_header_block .= "
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Imagen</th>
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Estilo</th>
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Color - Material</th>
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Marca</th>
                ";
            }

            $tallas_header_block .= "<th class='gris talla-grupo'>{$grupo}</th>";

            foreach ($set_info['tallas'] as $talla) {
                $numero = htmlspecialchars($talla['numero'], ENT_QUOTES, 'UTF-8');
                $tallas_header_block .= "<th class='gris'>{$numero}</th>";
            }

            $faltantes = $MAX_TALLAS - count($set_info['tallas']);

            if ($faltantes > 0) {
                if ($descripcion_set !== '') {
                    $tallas_header_block .= "<th colspan='{$faltantes}' class='gris talla-desc'>{$descripcion_set}</th>";
                } else {
                    $tallas_header_block .= "<th colspan='{$faltantes}' class='gris'>&nbsp;</th>";
                }
            }

            if ($primer_header) {
                $tallas_header_block .= "
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Total<br>Pares</th>
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Precio<br>por Par</th>
                    <th class='gris' rowspan='{$rowspan_encabezado}'>Monto Total<br>por Caja</th>
                ";
                $primer_header = false;
            }

            $tallas_header_block .= "</tr>";
        }

        $sql = mysql::getresult("SELECT idproducto, idproducto_precio, codigo AS producto, imagen, idtalla, cantidad, precio_venta, color, material, idset_talla, COALESCE(grupo, '') AS grupo, COALESCE(set_descripcion, '') AS set_descripcion
            FROM view_pedido_detalle
            WHERE idpedido = '$idpedido'
            ORDER BY grupo ASC, set_descripcion ASC, idproducto ASC, idproducto_precio ASC, idtalla ASC");

        if (!$sql) {
            $this->last_error = "Error al obtener el detalle del pedido para impresión.";
            $this->report_error(bd_error, $idpedido, $this->last_error);
            return false;
        }

        $meses = [
            '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
            '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto',
            '09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'
        ];

        $productos = [];

        while ($row = mysql::getrowresult($sql)) {

            $idp = $row['idproducto'].'_'.$row['idproducto_precio'].'_'.$row['idset_talla'];

            if (!isset($productos[$idp])) {
                $productos[$idp] = [
                    'producto' => $row['producto'],
                    'imagen'   => $row['imagen'],
                    'precio'   => $row['precio_venta'],
                    'color'    => $row['color'],
                    'material' => $row['material'],
                    'idset_talla' => (int)$row['idset_talla'],
                    'grupo' => trim((string)$row['grupo']),
                    'set_descripcion' => trim((string)$row['set_descripcion']),
                    'tallas'   => [],
                    'total'    => 0
                ];
            }

            $productos[$idp]['tallas'][$row['idtalla']] = $row['cantidad'];
            $productos[$idp]['total'] += $row['cantidad'];
        }

        $productos_chunks = array_chunk($productos, 8);
        $html_final = '';
        $total_hojas = count($productos_chunks);

        foreach ($productos_chunks as $index => $chunk) {

            $numero_hoja    = $index + 1;
            $detalle_html   = '';
            $total_pares    = 0;
            $total_general  = 0;

            foreach ($chunk as $p) {

                $fila_tallas = '';
                $set_producto = isset($sets_catalogo[$p['idset_talla']]) ? $sets_catalogo[$p['idset_talla']] : null;
                $grupo_producto = htmlspecialchars($p['grupo'], ENT_QUOTES, 'UTF-8');
                $fila_tallas .= "<td class='talla-grupo-producto'>{$grupo_producto}</td>";

                $cantidad_tallas = 0;

                if ($set_producto) {
                    foreach ($set_producto['tallas'] as $t) {
                        $cantidad = array_key_exists($t['idtalla'], $p['tallas']) ? $p['tallas'][$t['idtalla']] : '';
                        $fila_tallas .= "<td style='font-size: 13px;'>".($cantidad === '' ? '&nbsp;' : $cantidad)."</td>";
                        $cantidad_tallas++;
                    }
                }

                if ($cantidad_tallas < $MAX_TALLAS) {
                    $faltantes = $MAX_TALLAS - $cantidad_tallas;
                    $fila_tallas .= "<td colspan='{$faltantes}'>&nbsp;</td>";
                }

                $total  = $p['total'];
                $precio = $p['precio'];
                $monto  = $total * $precio;

                $total_pares    += $total;
                $total_general  += $monto;

                $detalle_html .= "
                <tr>
                    <td class='img'>
                        <img src='".($p['imagen'] ? '../'.$p['imagen'] : "https://via.placeholder.com/50")."'>
                    </td>
                    <td style='font-size: 12px;'>".$p['producto']."</td>
                    <td style='font-size: 10px;' class='center'>
                        ".strtoupper($p['color'])." - ".strtoupper($p['material'])."
                    </td>
                    <td style='font-size: 12px;' >".$PEDIDO['marca']."</td>
                    ".$fila_tallas."
                    <td>".$total."</td>
                    <td style='font-size: 12px;'>Q ".number_format($precio,2,'.',',')."</td>
                    <td style='font-size: 12px;'>Q ".number_format($monto,2,'.',',')."</td>
                </tr>
                ";
            }

            $DATA = [];
            $DATA['codigo_cliente']         = $PEDIDO['idcliente'];
            $fecha                          = strtotime($PEDIDO['fecha_creacion']);
            $DATA['fecha']                  = date('d', $fecha).' '.$meses[date('m', $fecha)].' '.date('Y', $fecha);
            $DATA['vendedor']               = $nombre_usuario;
            $DATA['cliente']                = $PEDIDO['cliente'];
            $DATA['nit']                    = !empty($PEDIDO['nit']) ? $PEDIDO['nit'] : 'CF';
            $DATA['telefono']               = $PEDIDO['telefono'];
            $DATA['direccion']              = $PEDIDO['direccion'];
            $DATA['nombre_zapateria']       = $PEDIDO['establecimiento'];
            $DATA['dias_credito']           = $PEDIDO['dias_credito'];
            $DATA['email']                  = $PEDIDO['email'];
            $fecha1                         = strtotime($PEDIDO['fecha_desde']);
            $fecha2                         = strtotime($PEDIDO['fecha_hasta']);
            $DATA['fecha_entrega']          = date('d', $fecha1).' '.$meses[date('m', $fecha1)].' - '.
                                                date('d', $fecha2).' '.$meses[date('m', $fecha2)].' '.date('Y', $fecha2);
            $DATA['transporte']             = $PEDIDO['transporte'];
            $DATA['idpedido']               = $PEDIDO['idpedido'];
            $DATA['descripcion_marca']      = $PEDIDO['descripcion_marca'];
            $DATA['tallas_header_block']    = $tallas_header_block;
            $DATA['detalle_productos']      = $detalle_html;
            $DATA['total_pares']            = $total_pares;
            $DATA['total']                  = number_format($total_general,2,'.',',');
            $DATA['observaciones']          = $PEDIDO['observaciones_pedido'];
            $DATA['numero_hoja']            = $numero_hoja;
            $DATA['total_hojas']            = $total_hojas;

            $html = new html('template_imprimir_pedido', $DATA);

            $html_final .= $html->get_html();

            if ($index < count($productos_chunks) - 1) {
                $html_final .= "<div style='page-break-after: always;'></div>";
            }
        }

        $security->registrar_bitacora($this->ACCIONES['imprimir_pedido'], $idpedido, $usuario);

        return $html_final;
    }

    public function estado($idpedido)
    {   
        return mysql::getvalue("SELECT estado FROM pedido WHERE idpedido = '$idpedido'");
    }
    
}
?>
