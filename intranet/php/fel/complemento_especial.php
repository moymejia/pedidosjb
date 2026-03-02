
<?php
class ComplementoEspecial {

    private $id_complemento;


    private $nombre_complemento;


    private $uri_complemento;


    private $retencion_isr;


    private $retencion_iva;


    private $total_menos_retenciones;

    public function getIdComplemento() {
        return $this->id_complemento;
    }

    public function setIdComplemento($idComplemento) {
        $this->id_complemento = $idComplemento;
    }

    public function getNombreComplemento() {
        return $this->nombre_complemento;
    }

    public function setNombreComplemento($nombreComplemento) {
        $this->nombre_complemento = $nombreComplemento;
    }

    public function getUriComplemento() {
        return $this->uri_complemento;
    }

    public function setUriComplemento($uriComplemento) {
        $this->uri_complemento = $uriComplemento;
    }

    public function getRetencionIsr() {
        return $this->retencion_isr;
    }

    public function setRetencionIsr($retencionIsr) {
        $this->retencion_isr = $retencionIsr;
    }

    public function getRetencionIva() {
        return $this->retencion_iva;
    }

    public function setRetencionIva($retencionIva) {
        $this->retencion_iva = $retencionIva;
    }

    public function getTotalMenosRetenciones() {
        return $this->total_menos_retenciones;
    }

    public function setTotalMenosRetenciones($totalMenosRetenciones) {
        $this->total_menos_retenciones = $totalMenosRetenciones;
    }
}
