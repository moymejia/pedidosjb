<?php
class ImpuestosDetalle  {

    private $nombre_corto;

    private $codigo_unidad_gravable;

    private $monto_gravable;

    private $cantidad_unidades_gravables;

    private $monto_impuesto;

    private $total;

    public function getNombreCorto() {
        return $this->nombre_corto;
    }

    public function setNombreCorto($nombreCorto) {
        $this->nombre_corto = $nombreCorto;
    }

    public function getCodigoUnidadGravable() {
        return $this->codigo_unidad_gravable;
    }

    public function setCodigoUnidadGravable($codigoUnidadGravable) {
        $this->codigo_unidad_gravable = $codigoUnidadGravable;
    }

    public function getMontoGravable() {
        return $this->monto_gravable;
    }

    public function setMontoGravable($montoGravable) {
        $this->monto_gravable = $montoGravable;
    }

    public function getCantidadUnidadesGravables() {
        return $this->cantidad_unidades_gravables;
    }

    public function setCantidadUnidadesGravables($cantidadUnidadesGravables) {
        $this->cantidad_unidades_gravables = $cantidadUnidadesGravables;
    }

    public function getMontoImpuesto() {
        return $this->monto_impuesto;
    }

    public function setMontoImpuesto($montoImpuesto) {
        $this->monto_impuesto = $montoImpuesto;
    }

    public function getTotal() {
        return $this->total;
    }

    public function setTotal($total) {
        $this->total = $total;
    }
}
