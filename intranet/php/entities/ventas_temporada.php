<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/html.php';
require_once '../wisetech/objects.php';
require_once '../wisetech/utils.php';
require_once('../wisetech/report.php');
require_once('../entities/temporada.php');
require_once('../entities/cliente.php');
require_once('../entities/marca.php');

class ventas_temporada extends table
{

    use utils;
    private $idventas_temporada;
    public $last_error = '';
    private $ACCIONES   = [];

    public function __construct($PARAMETROS = null)
    {

        parent::__construct(prefijo . '_pedidos', 'ventas_temporada');

        $this->ACCIONES['opcion_ventas_temporada'] = 244;

        if (isset($PARAMETROS['operacion'])) {
            if ($PARAMETROS['operacion'] == 'reporte_ventas_temporada') {
                if ($resultado = $this->reporte_ventas_temporada($PARAMETROS['idtemporada'], $PARAMETROS['idcliente'], $PARAMETROS['idmarca'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'options_clientes_temporada_marca') {
                if ($resultado = $this->options_clientes_temporada_marca($PARAMETROS['idtemporada'], $PARAMETROS['idmarca'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }

            if ($PARAMETROS['operacion'] == 'options_marcas_temporada_cliente') {
                if ($resultado = $this->options_marcas_temporada_cliente($PARAMETROS['idtemporada'], $PARAMETROS['idcliente'])) {
                    self::end_success($resultado);
                } else {
                    self::end_error($this->last_error);
                }
            }
        }

    }

    public function cargar_opcion()
    {
        $DATA                       = [];
        $DATA['temporadas_activas'] = (new temporada())->option_activos();
        $html                = new html('ventas_temporada', $DATA);

        return $html->get_html();
    }

    public function options_clientes_temporada_marca($idtemporada, $idmarca)
    {
        $where_marca = ($idmarca > 0) ? "AND idmarca = '$idmarca'" : '';

        return mysql::getoptions("SELECT DISTINCT idcliente AS id, nombre_cliente AS descripcion
            FROM view_ventas_temporada
            WHERE idtemporada = '$idtemporada' $where_marca
            ORDER BY nombre_cliente ASC");
    }

    public function options_marcas_temporada_cliente($idtemporada, $idcliente)
    {   

        $idcliente_condicion = ($idcliente > 0) ? "AND idcliente = '$idcliente'" : '';

        return mysql::getoptions("SELECT DISTINCT idmarca AS id, nombre_marca AS descripcion
            FROM view_ventas_temporada
            WHERE idtemporada = '$idtemporada' $idcliente_condicion
            ORDER BY nombre_marca ASC");
    }

    public function reporte_ventas_temporada($idtemporada, $idcliente, $idmarca)
    {
        if ($idtemporada == '') {
            $this->last_error = 'Debe seleccionar una temporada';
            utils::report_error(validation_error, $idtemporada, $this->last_error);
            return false;
        }

        $where_marca = ($idmarca > 0) ? "AND idmarca = '$idmarca'" : '';
        $where_cliente = ($idcliente > 0) ? "AND idcliente = '$idcliente'" : '';
        $condicion_sql = " idtemporada = '$idtemporada' AND estado = 'CERRADO' $where_marca $where_cliente";
        
        $sql = mysql::getresult("
            SELECT 
                CONCAT(
                    '<button class=\"btn btn-primary btn-sm\" onclick=\"abrir_pedido(', idpedido, ')\" style=\"cursor:pointer; margin-right:5px;\">',
                        '<i class=\"fas fa-eye\"></i>',
                    '</button>',
                    nopedido
                ) AS `No. Pedido`,
                nombre_cliente AS cliente,
                nombre_marca AS marca,
                cantidad_pares AS `cantidad de pares`,
                cantidad_modelos AS `cantidad de modelos`,
                CONCAT('Q ', FORMAT(monto_total, 2)) AS `monto total`
            FROM view_ventas_temporada 
            WHERE $condicion_sql
        ");

        $PARAMETROS = [];
        $PARAMETROS['print'] = true;
        $PARAMETROS['excel'] = true;

        $report = new report("Reporte de pedidos", $PARAMETROS);

        $alineacion = [
            'cantidad de pares' => 'center',
            'cantidad de modelos' => 'center',
            'total de pedidos' => 'center',
            'total de clientes' => 'center',
            'total de marcas' => 'center',
            'total de pares' => 'center',
            'total de modelos' => 'center',
            'monto total' => 'right'
        ];

        $report->addTitle("Reporte de pedidos cerrados");
        $report->addtable($sql, "", "", "", [], $alineacion, []);
        $report->addBreakLine();


        $resumen = mysql::getresult("
                SELECT 'Cantidad total de pedidos' AS concepto, COUNT(DISTINCT idpedido) AS valor
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de clientes', COUNT(DISTINCT idcliente)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de marcas', COUNT(DISTINCT idmarca)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de pares', SUM(cantidad_pares)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Cantidad total de modelos diferentes', SUM(cantidad_modelos)
                FROM view_ventas_temporada
                WHERE $condicion_sql
                UNION ALL
                SELECT 'Monto total', CONCAT('Q ', FORMAT(SUM(monto_total), 2))
                FROM view_ventas_temporada
                WHERE $condicion_sql
            ");

            $alineacion = [
                'concepto' => 'right',
                'valor' => 'right',
        ];
            
        $report->addTitle("Resumen general de ventas por temporada");
        $report->addtable($resumen, "", "", "", [], $alineacion, []);

        return $report->getReport();
    }

}