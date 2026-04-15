<?php
require_once('../wisetech/table.php');
require_once('../wisetech/security.php');
require_once('../wisetech/html.php');
require_once('../wisetech/utils.php');
require_once('../entities/pedido.php');

class despacho extends table
{
	use utils;

	private $last_error;
	private $ACCIONES = array();

	public function __construct($PARAMETROS = null)
	{
		parent::__construct(prefijo . '_pedidos', 'despacho');

		$this->ACCIONES['Opcion_despacho']            = 243;
		$this->ACCIONES['Crear_despacho']    		  = 244;
		$this->ACCIONES['Despachar_lineas']           = 245;
		$this->ACCIONES['Cerrar_despacho']            = 246;

		if (isset($PARAMETROS['operacion'])) {
			if ($PARAMETROS['operacion'] == 'obtener_detalle_despacho') {
				if ($this->validate_parameter_existence(['iddespacho'], $PARAMETROS, false)) {
					if ($resultado = $this->obtener_detalle_despacho($PARAMETROS['iddespacho'])) {
						self::end_success($resultado);
					} else {
						self::end_error($this->last_error);
					}
				} else {
					self::end_error('Faltan parámetros');
				}
			}

			if ($PARAMETROS['operacion'] == 'crear_despacho') {
				if ($this->validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
					if ($resultado = $this->crear_despacho($PARAMETROS)) {
						self::end_success($resultado);
					} else {
						self::end_error($this->last_error);
					}
				} else {
					self::end_error('Faltan parámetros');
				}
			}

			if ($PARAMETROS['operacion'] == 'obtener_detalle_pedido') {
				if ($this->validate_parameter_existence(['idpedido'], $PARAMETROS, false)) {
					if ($resultado = $this->obtener_detalle_pedido($PARAMETROS['idpedido'])) {
						self::end_success($resultado);
					} else {
						self::end_error($this->last_error);
					}
				} else {
					self::end_error('Faltan parámetros');
				}
			}

			if ($PARAMETROS['operacion'] == 'despachar_lineas') {
				if ($this->validate_parameter_existence(['iddespacho', 'idpedido', 'ids_detalle'], $PARAMETROS, false)) {
					if ($resultado = $this->despachar_lineas($PARAMETROS)) {
						self::end_success($resultado);
					} else {
						self::end_error($this->last_error);
					}
				} else {
					self::end_error('Faltan parámetros');
				}
			}

			if ($PARAMETROS['operacion'] == 'cerrar_despacho') {
				if ($this->validate_parameter_existence(['iddespacho'], $PARAMETROS, false)) {
					if ($resultado = $this->cerrar_despacho($PARAMETROS['iddespacho'])) {
						self::end_success($resultado);
					} else {
						self::end_error($this->last_error);
					}
				} else {
					self::end_error('Faltan parámetros');
				}
			}
		}
	}

	public function cargar_opcion()
	{
		$security = new security($this->ACCIONES['opcion_despacho']);
		$security->get_actual_user();

		$DATA = [];
		$DATA['tabla_despachos_proceso'] = $this->tabla_despachos_proceso();
		$DATA['tabla_pedidos_cerrados'] = $this->tabla_pedidos_cerrados();

		$html = new html('despacho', $DATA);
		return $html->get_html();
	}

	private function tabla_despachos_proceso()
	{
		$sql = mysql::getresult("SELECT iddespacho, idpedido, nopedido, cliente, temporada, marca, fecha,
				monto_total, estado, lineas_pendientes
			FROM view_despachos_proceso
			ORDER BY iddespacho DESC");

		if (!$sql) {
			$this->last_error = 'Error al cargar despachos en proceso.';
			utils::report_error(bd_error, 'tabla_despachos_proceso', $this->last_error);
			return "<div class='alert alert-danger'>" . $this->last_error . "</div>";
		}

		$columnControl = true;
		$responsive    = true;
		$colReorder    = true;
		$select        = false;
		$buttons       = true;
		$paging        = true;
		$ordering      = true;
		$order         = true;
		$rowGroup      = false;
		$tituloTabla   = 'Despachos en proceso';
		$fileName      = 'Despachos_Proceso';

		$data_ = "";
		$data_  = " data-conf-columncontrol='" . ($columnControl ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-rowgroup=''";
		$data_ .= " data-conf-titulotabla='" . $tituloTabla . "' ";
		$data_ .= " data-conf-filename='" . $fileName . "' ";
		$data_ .= " data-conf-responsive='" . ($responsive ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-colreorder='" . ($colReorder ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-select='" . ($select ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-buttons='" . ($buttons ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-paging='" . ($paging ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-ordering='" . ($ordering ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-noorder='" . (!$order ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-rowgroup='" . (!$rowGroup ? 'true' : 'false') . "' ";

		$tabla = "<table id='tabla_datos' " . $data_ . " class='display nowrap table table-hover table-bordered datatable' cellspacing='0' width='100%'>
			<thead>
				<tr>
					<th>Acciones</th>
					<th>Despacho</th>
					<th>No. pedido</th>
					<th>Cliente</th>
					<th>Temporada</th>
					<th>Marca</th>
					<th>Fecha</th>
					<th>Monto</th>
					<th>Lineas pendientes</th>
					<th>Estado</th>
				</tr>
			</thead>
			<tbody>";

		while ($row = mysql::getrowresult($sql)) {
			$iddespacho = $row['iddespacho'];
			$idpedido = $row['idpedido'];
			$nopedido = $row['nopedido'];
			$cliente = $row['cliente'];
			$temporada = $row['temporada'];
			$marca = $row['marca'];
			$fecha = $row['fecha'];
			$monto_total = number_format((float)$row['monto_total'], 2);
			$lineas_pendientes = (int)$row['lineas_pendientes'];
			$estado = strtoupper(trim($row['estado']));

			$tabla .= "<tr>
				<td>
					<button class='btn btn-sm btn-primary waves-effect waves-light' type='button' onclick='despachoSeleccionarExistente(" . $iddespacho . ", " . $idpedido . ", \"" . $nopedido . "\")'>Seleccionar</button>
				</td>
				<td>#" . $iddespacho . "</td>
				<td>" . $nopedido . "</td>
				<td>" . $cliente . "</td>
				<td>" . $temporada . "</td>
				<td>" . $marca . "</td>
				<td>" . $fecha . "</td>
				<td class='text-right'>Q " . $monto_total . "</td>
				<td class='text-center'>" . $lineas_pendientes . "</td>
				<td class='text-center'>" . $estado . "</td>
			</tr>";
		}

		$tabla .= '</tbody></table>';
		return $tabla;
	}

	private function tabla_pedidos_cerrados()
	{
		$sql = mysql::getresult("SELECT idpedido, nopedido, cliente, temporada, marca, fecha_desde, fecha_hasta, estado
			FROM view_pedidos
			WHERE estado = 'CERRADO'
			ORDER BY idpedido DESC");

		if (!$sql) {
			$this->last_error = 'Error al cargar pedidos cerrados.';
			utils::report_error(bd_error, 'tabla_pedidos_cerrados', $this->last_error);
			return "<div class='alert alert-danger'>" . $this->last_error . "</div>";
		}

		$columnControl = true;
		$responsive    = true;
		$colReorder    = true;
		$select        = false;
		$buttons       = true;
		$paging        = true;
		$ordering      = true;
		$order         = true;
		$rowGroup      = false;
		$tituloTabla   = 'Pedidos cerrados para despacho';
		$fileName      = 'Despachos';

		$data_ = "";
		$data_  = " data-conf-columncontrol='" . ($columnControl ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-rowgroup=''";
		$data_ .= " data-conf-titulotabla='" . $tituloTabla . "' ";
		$data_ .= " data-conf-filename='" . $fileName . "' ";
		$data_ .= " data-conf-responsive='" . ($responsive ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-colreorder='" . ($colReorder ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-select='" . ($select ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-buttons='" . ($buttons ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-paging='" . ($paging ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-ordering='" . ($ordering ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-noorder='" . (!$order ? 'true' : 'false') . "' ";
		$data_ .= " data-conf-rowgroup='" . (!$rowGroup ? 'true' : 'false') . "' ";

		$tabla = "<table id='tabla_datos' " . $data_ . " class='display nowrap table table-hover table-bordered datatable' cellspacing='0' width='100%'>
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
			<tbody>";

		while ($row = mysql::getrowresult($sql)) {
			$idpedido    = $row['idpedido'];
			$nopedido    = $row['nopedido'];
			$cliente     = $row['cliente'];
			$temporada   = $row['temporada'];
			$marca       = $row['marca'];
			$fecha_desde = $row['fecha_desde'];
			$fecha_hasta = $row['fecha_hasta'];
			$estado      = $row['estado'];

			$tabla .= "<tr>
				<td>
					<button class='btn btn-sm btn-primary waves-effect waves-light' type='button' onclick='despachoSeleccionarPedido(" . $idpedido . ", \"" . $nopedido . "\")'>Seleccionar</button>
				</td>
				<td>" . $nopedido . "</td>
				<td>" . $cliente . "</td>
				<td>" . $temporada . "</td>
				<td>" . $marca . "</td>
				<td>" . $fecha_desde . "</td>
				<td>" . $fecha_hasta . "</td>
				<td>" . $estado . "</td>
			</tr>";
		}

		$tabla .= '</tbody></table>';
		return $tabla;
	}

	public function obtener_detalle_pedido($idpedido)
	{
		$security = new security($this->ACCIONES['opcion_despacho']);
		$security->get_actual_user();

		$idpedido = addslashes($idpedido);
		$_PEDIDO = new pedido();
		$estado_pedido = $_PEDIDO->estado($idpedido);

		if (!$estado_pedido) {
			$this->last_error = 'Pedido no encontrado.';
			utils::report_error(validation_error, $idpedido, $this->last_error);
			return false;
		}

		if ($estado_pedido !== 'CERRADO') {
			$this->last_error = 'El pedido debe estar en estado CERRADO.';
			utils::report_error(validation_error, $idpedido, $this->last_error);
			return false;
		}

		return $this->detalle_pedido_json($idpedido);
	}

	public function obtener_detalle_despacho($iddespacho)
	{
		$security = new security($this->ACCIONES['opcion_despacho']);
		$security->get_actual_user();

		$iddespacho = addslashes($iddespacho);

		$row_despacho = mysql::getrow("SELECT iddespacho, idpedido, estado, monto_flete, monto_otros, numero_factura, observaciones, fecha, fecha_factura
			FROM despacho
			WHERE iddespacho = '" . $iddespacho . "'
			LIMIT 1");

		if (!$row_despacho) {
			$this->last_error = 'Despacho no encontrado.';
			utils::report_error(validation_error, $iddespacho, $this->last_error);
			return false;
		}

		if (($row_despacho['estado'] . '') !== 'ACTIVO' && ($row_despacho['estado'] . '') !== 'CERRADO') {
			$this->last_error = 'El despacho no se encuentra en un estado gestionable.';
			utils::report_error(validation_error, $row_despacho, $this->last_error);
			return false;
		}

		$_PEDIDO = new pedido();
		$estado_pedido = $_PEDIDO->estado($row_despacho['idpedido']);

		if (!$estado_pedido) {
			$this->last_error = 'Pedido no encontrado.';
			utils::report_error(validation_error, $row_despacho['idpedido'], $this->last_error);
			return false;
		}

		if ($estado_pedido !== 'CERRADO') {
			$this->last_error = 'El pedido debe estar en estado CERRADO.';
			utils::report_error(validation_error, $row_despacho['idpedido'], $this->last_error);
			return false;
		}

		$detalle = json_decode($this->detalle_pedido_json($row_despacho['idpedido']), true);
		if (!is_array($detalle)) {
			$this->last_error = 'No se pudo obtener el detalle del despacho.';
			utils::report_error(validation_error, $row_despacho, $this->last_error);
			return false;
		}

		$detalle['iddespacho'] = (int)$row_despacho['iddespacho'];
		$detalle['idpedido'] = (int)$row_despacho['idpedido'];
		$detalle['monto_flete'] = (float)$row_despacho['monto_flete'];
		$detalle['monto_otros'] = (float)$row_despacho['monto_otros'];
		$detalle['numero_factura'] = $row_despacho['numero_factura'];
		$detalle['observaciones'] = $row_despacho['observaciones'];
		$detalle['fecha'] = $row_despacho['fecha'];
		$detalle['fecha_factura'] = $row_despacho['fecha_factura'];
		$detalle['nopedido'] = mysql::getvalue("SELECT nopedido FROM view_pedidos WHERE idpedido = '" . addslashes($row_despacho['idpedido']) . "' LIMIT 1");
		$detalle['estado_despacho'] = $row_despacho['estado'];

		return json_encode($detalle);
	}

	public function crear_despacho($PARAMETROS)
	{
		$security = new security($this->ACCIONES['Crear_despacho']);
		$usuario  = $security->get_actual_user();

		$idpedido = addslashes($PARAMETROS['idpedido']);
		$monto_flete = isset($PARAMETROS['monto_flete']) ? (float)$PARAMETROS['monto_flete'] : 0;
		$monto_otros = isset($PARAMETROS['monto_otros']) ? (float)$PARAMETROS['monto_otros'] : 0;
		$_PEDIDO = new pedido();
		$estado_pedido = $_PEDIDO->estado($idpedido);

		if (!$estado_pedido) {
			$this->last_error = 'Pedido no encontrado.';
			utils::report_error(validation_error, $idpedido, $this->last_error);
			return false;
		}

		if ($estado_pedido !== 'CERRADO') {
			$this->last_error = 'El pedido debe estar en estado CERRADO.';
			utils::report_error(validation_error, $idpedido, $this->last_error);
			return false;
		}

		if ($monto_flete <= 0) {
			$this->last_error = 'El monto de flete es obligatorio y debe ser mayor a cero.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		if ($monto_otros < 0) {
			$this->last_error = 'El monto otros no puede ser negativo.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		$pendientes = mysql::getvalue("SELECT COUNT(*)
			FROM pedido_detalle
			WHERE idpedido = '" . addslashes($idpedido) . "'
			AND IFNULL(cantidad_pendiente, 0) > 0");

		if ((int)$pendientes <= 0) {
			$this->last_error = 'El pedido ya no tiene detalles pendientes para un nuevo despacho.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		$numero_factura = isset($PARAMETROS['numero_factura']) ? trim($PARAMETROS['numero_factura']) : '';
		$observaciones  = isset($PARAMETROS['observaciones']) ? trim($PARAMETROS['observaciones']) : '';
		$fecha_factura  = isset($PARAMETROS['fecha_factura']) ? trim($PARAMETROS['fecha_factura']) : '';

		if (empty($numero_factura)) {
			$this->last_error = 'El número de factura es obligatorio.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		if (empty($fecha_factura)) {
			$this->last_error = 'La fecha de factura es obligatoria.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		if (strlen($numero_factura) > 50) {
			$this->last_error = 'El número de factura no puede exceder 50 caracteres.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		if (!empty($observaciones) && strlen($observaciones) > 500) {
			$this->last_error = 'Las observaciones no pueden exceder 500 caracteres.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		$DATOS = [];
		$DATOS['idpedido']       = $idpedido;
		$DATOS['fecha']          = date('Y-m-d');
		if (!empty($numero_factura)) $DATOS['numero_factura'] = $numero_factura;
		if (!empty($observaciones))  $DATOS['observaciones']  = $observaciones;
		if (!empty($fecha_factura))  $DATOS['fecha_factura']  = $fecha_factura;
		$DATOS['monto_flete']    = $monto_flete;
		$DATOS['monto_otros']    = $monto_otros;
		$DATOS['monto_subtotal'] = 0;
		$DATOS['monto_total']    = round($monto_flete + $monto_otros, 2);
		$DATOS['saldo_pendiente']  = round($monto_flete + $monto_otros, 2);
		$DATOS['estado']           = 'ACTIVO';
		$DATOS['usuario_creacion'] = $usuario;

		if (!$this->insert_record($DATOS)) {
			$this->last_error = 'Error al crear el despacho.';
			utils::report_error(bd_error, $DATOS, $this->last_error);
			return false;
		}

		$iddespacho = mysql::last_id();
		$security->registrar_bitacora($this->ACCIONES['Crear_despacho'], $iddespacho, 'CREAR_DESPACHO_MANUAL');

		return json_encode([
			'iddespacho' => (int)$iddespacho,
			'idpedido' => (int)$idpedido
		]);
	}

	public function cerrar_despacho($iddespacho)
	{
		$security = new security($this->ACCIONES['Cerrar_despacho']);
		$usuario  = $security->get_actual_user();

		$iddespacho = addslashes($iddespacho);

		$row_despacho = mysql::getrow("SELECT iddespacho, estado
			FROM despacho
			WHERE iddespacho = '" . $iddespacho . "'
			LIMIT 1");

		if (!$row_despacho) {
			$this->last_error = 'Despacho no encontrado.';
			utils::report_error(validation_error, $iddespacho, $this->last_error);
			return false;
		}

		if (($row_despacho['estado'] . '') !== 'ACTIVO') {
			$this->last_error = 'Solo se pueden cerrar despachos en estado ACTIVO.';
			utils::report_error(validation_error, $row_despacho, $this->last_error);
			return false;
		}

		$total_detalles = mysql::getvalue("SELECT COUNT(*)
			FROM despacho_detalle
			WHERE iddespacho = '" . $iddespacho . "'");

		if ((int)$total_detalles <= 0) {
			$this->last_error = 'No se puede cerrar un despacho sin detalles despachados.';
			utils::report_error(validation_error, $iddespacho, $this->last_error);
			return false;
		}

		$DATOS = [];
		$DATOS['iddespacho'] = $iddespacho;
		$DATOS['estado'] = 'CERRADO';
		$DATOS['fecha_modificacion'] = date('Y-m-d H:i:s');
		$DATOS['usuario_modificacion'] = $usuario;

		if (!$this->update_record($DATOS, ['iddespacho'])) {
			$this->last_error = 'Error al cerrar el despacho.';
			utils::report_error(bd_error, $DATOS, $this->last_error);
			return false;
		}

		$security->registrar_bitacora($this->ACCIONES['Cerrar_despacho'], $iddespacho, 'ACTIVO->CERRADO');
		return true;
	}

	private function detalle_pedido_json($idpedido)
	{
		$idpedido = addslashes($idpedido);

		$result = mysql::getresult("SELECT *
			FROM view_despacho_detalle
			WHERE idpedido = " . $idpedido . "
			ORDER BY codigo ASC, color ASC, material ASC, idpedido_detalle ASC");

		if (!$result) {
			$this->last_error = 'Error al obtener detalle del pedido.';
			utils::report_error(bd_error, $idpedido, $this->last_error);
			return false;
		}

		$DATA = [];
		$total_lineas = 0;
		$total_pares = 0;
		$total_monto = 0.0;

		while ($row = mysql::getrowresult($result)) {
			$cantidad_original   = (int)$row['cantidad'];
			$cantidad_despachada = (int)$row['cantidad_despachada'];
			$cantidad_pendiente  = (int)$row['cantidad_pendiente'];

			if ($cantidad_pendiente <= 0 && $cantidad_original > 0 && $cantidad_despachada <= 0) {
				$cantidad_pendiente = $cantidad_original;
			}

			if ($cantidad_pendiente < 0) {
				$cantidad_pendiente = 0;
			}

			$estado_linea = 'ACTIVO';
			if ($cantidad_pendiente <= 0) {
				$estado_linea = 'DESPACHADO';
			} elseif ($cantidad_despachada > 0) {
				$estado_linea = 'PARCIAL';
			}

			$row['idproducto'] = isset($row['idproducto']) ? $row['idproducto'] : 0;
			$row['idset_talla'] = isset($row['idset_talla']) ? $row['idset_talla'] : 0;
			if (!isset($row['set_talla']) || trim($row['set_talla'] . '') === '') {
				$grupo = isset($row['grupo']) ? trim($row['grupo'] . '') : '';
				$set_desc = isset($row['set_descripcion']) ? trim($row['set_descripcion'] . '') : '';
				$row['set_talla'] = trim($grupo . ' - ' . $set_desc, ' -');
			}
			if (!isset($row['marca'])) {
				$row['marca'] = '';
			}
			$row['cantidad_despachada'] = $cantidad_despachada;
			$row['estado'] = $estado_linea;
			$row['cantidad_pendiente'] = $cantidad_pendiente;
			$row['precio_venta'] = (float)$row['precio_venta'];
			$row['precio_venta_formateado'] = 'Q ' . number_format((float)$row['precio_venta'], 2);
			$row['subtotal_pendiente'] = round($cantidad_pendiente * $row['precio_venta'], 2);

			if ($cantidad_original > 0) {
				$total_lineas++;
				$total_pares += $cantidad_original;
				$total_monto += round($cantidad_original * $row['precio_venta'], 2);
			}
			$DATA[] = $row;
		}

		return json_encode([
			'detalle' => $DATA,
			'resumen' => [
				'lineas' => $total_lineas,
				'pares' => $total_pares,
				'monto' => round($total_monto, 2),
				'monto_formateado' => 'Q ' . number_format(round($total_monto, 2), 2)
			]
		]);
	}

	public function despachar_lineas($PARAMETROS)
	{
		$security = new security($this->ACCIONES['Despachar_lineas']);
		$usuario  = $security->get_actual_user();

		$iddespacho = addslashes($PARAMETROS['iddespacho']);
		$idpedido = addslashes($PARAMETROS['idpedido']);

		$row_despacho = mysql::getrow("SELECT iddespacho, idpedido, estado, monto_subtotal, monto_flete, monto_otros
			FROM despacho
			WHERE iddespacho = '" . $iddespacho . "'
			LIMIT 1");

		if (!$row_despacho) {
			$this->last_error = 'Despacho no encontrado.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		if (($row_despacho['estado'] . '') !== 'ACTIVO') {
			$this->last_error = 'El despacho seleccionado no esta ACTIVO.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		if (($row_despacho['idpedido'] . '') !== ($idpedido . '')) {
			$this->last_error = 'El pedido no coincide con el despacho seleccionado.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		$_PEDIDO = new pedido();
		$estado_pedido = $_PEDIDO->estado($idpedido);

		if (!$estado_pedido) {
			$this->last_error = 'Pedido no encontrado.';
			utils::report_error(validation_error, $idpedido, $this->last_error);
			return false;
		}

		if ($estado_pedido !== 'CERRADO') {
			$this->last_error = 'El pedido debe estar en estado CERRADO.';
			utils::report_error(validation_error, $idpedido, $this->last_error);
			return false;
		}

		$ARRAY = $PARAMETROS['ids_detalle'];
		if (!is_array($ARRAY)) {
			$ARRAY = [$ARRAY];
		}

		$ARRAY = array_values(array_filter($ARRAY, function ($value) {
			return trim($value . '') !== '';
		}));

		if (count($ARRAY) == 0) {
			$this->last_error = 'Debe seleccionar al menos una línea ACTIVA.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		$monto_flete = isset($PARAMETROS['monto_flete']) ? $PARAMETROS['monto_flete'] : 0;
		$monto_otros = isset($PARAMETROS['monto_otros']) ? $PARAMETROS['monto_otros'] : 0;
		if ($monto_flete < 0 || $monto_otros < 0) {
			$this->last_error = 'Los montos de flete y otros no pueden ser negativos.';
			utils::report_error(validation_error, $PARAMETROS, $this->last_error);
			return false;
		}

		$subtotal_despacho = 0.0;
		$lineas_validadas = [];

		foreach ($ARRAY as $idpedido_detalle) {
			$iddespacho_detalle = mysql::getvalue("SELECT iddespacho
				FROM view_despacho_lineas_estado
				WHERE idpedido_detalle = '" . addslashes($idpedido_detalle) . "'
				AND IFNULL(estado_despacho, '') <> 'ANULADO'
				LIMIT 1");

			if (!empty($iddespacho_detalle) && ($iddespacho_detalle . '') !== ($iddespacho . '')) {
				$this->last_error = 'La línea ' . $idpedido_detalle . ' ya pertenece al despacho #' . $iddespacho_detalle . '. Debe gestionarla en ese despacho.';
				utils::report_error(validation_error, $idpedido_detalle, $this->last_error);
				return false;
			}

			$row_detalle = mysql::getrow("SELECT idpedido, cantidad_pendiente, precio_venta, cantidad_despachada
				FROM pedido_detalle
				WHERE idpedido_detalle = '" . addslashes($idpedido_detalle) . "'
				LIMIT 1");

			if (!$row_detalle) {
				$this->last_error = 'La línea seleccionada no existe.';
				utils::report_error(validation_error, $idpedido_detalle, $this->last_error);
				return false;
			}

			if (($row_detalle['idpedido'] . '') !== ($idpedido . '')) {
				$this->last_error = 'La línea no pertenece al pedido seleccionado.';
				utils::report_error(validation_error, $idpedido_detalle, $this->last_error);
				return false;
			}

			$cantidad_pendiente = $row_detalle['cantidad_pendiente'];
			if ($cantidad_pendiente <= 0) {
				$this->last_error = 'No se puede despachar una línea sin cantidad pendiente.';
				utils::report_error(validation_error, $idpedido_detalle, $this->last_error);
				return false;
			}

			$precio_venta = $row_detalle['precio_venta'];
			$subtotal_linea = round($cantidad_pendiente * $precio_venta, 2);
			$subtotal_despacho += $subtotal_linea;

			$lineas_validadas[] = [
				'idpedido_detalle' => $idpedido_detalle,
				'cantidad_pendiente' => $cantidad_pendiente,
				'cantidad_despachada' => $row_detalle['cantidad_despachada'],
				'precio_venta' => $precio_venta,
				'subtotal' => $subtotal_linea
			];
		}

		$subtotal_acumulado = (float)$row_despacho['monto_subtotal'] + (float)$subtotal_despacho;
		$monto_total = round($subtotal_acumulado + $monto_flete + $monto_otros, 2);

		$DATOS_DESPACHO = [];
		$DATOS_DESPACHO['iddespacho'] = $iddespacho;
		$DATOS_DESPACHO['monto_flete'] = $monto_flete;
		$DATOS_DESPACHO['monto_otros'] = $monto_otros;
		$DATOS_DESPACHO['monto_subtotal'] = $subtotal_acumulado;
		$DATOS_DESPACHO['monto_total'] = $monto_total;
		$DATOS_DESPACHO['saldo_pendiente'] = $monto_total;
		$DATOS_DESPACHO['fecha_modificacion'] = date('Y-m-d H:i:s');
		$DATOS_DESPACHO['usuario_modificacion'] = $usuario;

		if (!$this->update_record($DATOS_DESPACHO, ['iddespacho'])) {
			$this->last_error = 'Error al actualizar encabezado del despacho.';
			utils::report_error(bd_error, $DATOS_DESPACHO, $this->last_error);
			return false;
		}

		$TABLA_DESPACHO_DETALLE = new table(prefijo . '_pedidos', 'despacho_detalle');
		$TABLA_PEDIDO_DETALLE = new table(prefijo . '_pedidos', 'pedido_detalle');

		foreach ($lineas_validadas as $linea) {
			$datos_detalle = [];
			$datos_detalle['iddespacho'] = $iddespacho;
			$datos_detalle['idpedido_detalle'] = $linea['idpedido_detalle'];
			$datos_detalle['cantidad'] = $linea['cantidad_pendiente'];
			$datos_detalle['precio_venta'] = $linea['precio_venta'];
			$datos_detalle['subtotal'] = $linea['subtotal'];
			$datos_detalle['estado'] = 'DESPACHADO';
			$datos_detalle['usuario_creacion'] = $usuario;

			if (!$TABLA_DESPACHO_DETALLE->insert_record($datos_detalle)) {
				$this->last_error = 'Error al guardar detalle del despacho.';
				utils::report_error(bd_error, $datos_detalle, $this->last_error);
				return false;
			}

			$nuevo_cantidad_despachada = $linea['cantidad_despachada'] + $linea['cantidad_pendiente'];
			$nuevo_cantidad_pendiente = $linea['cantidad_pendiente'] - $linea['cantidad_pendiente'];

			$DATOS_ACTUALIZAR = [];
			$DATOS_ACTUALIZAR['idpedido_detalle'] = $linea['idpedido_detalle'];
			$DATOS_ACTUALIZAR['cantidad_despachada'] = $nuevo_cantidad_despachada;
			$DATOS_ACTUALIZAR['cantidad_pendiente'] = $nuevo_cantidad_pendiente;
			$DATOS_ACTUALIZAR['fecha_modificacion'] = date('Y-m-d H:i:s');
			$DATOS_ACTUALIZAR['usuario_modificacion'] = $usuario;

			if (!$TABLA_PEDIDO_DETALLE->update_record($DATOS_ACTUALIZAR, ['idpedido_detalle'])) {
				$this->last_error = 'Error al actualizar estado de una línea.';
				utils::report_error(bd_error, $linea, $this->last_error);
				return false;
			}

			$security->registrar_bitacora($this->ACCIONES['Despachar_lineas'], $linea['idpedido_detalle'], 'PENDIENTE->DESPACHADO');
		}

		return json_encode(['iddespacho' => (int)$iddespacho]);
	}

}
?>
