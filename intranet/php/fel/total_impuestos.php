<?php
class TotalImpuestos {

    private $nombre_corto;

    private $total_monto_impuesto;

    public function getNombreCorto() {
        return $this->nombre_corto;
    }

    public function setNombreCorto($nombreCorto) {
        $this->nombre_corto = $nombreCorto;
    }

    public function getTotalMontoImpuesto() {
        return $this->total_monto_impuesto;
    }

    public function setTotalMontoImpuesto($totalMontoImpuesto) {
        $this->total_monto_impuesto = $totalMontoImpuesto;
    }
}