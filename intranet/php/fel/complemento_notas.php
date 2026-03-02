
<?php
class ComplementoNotas  {

    private $id_complemento;

    private $nombre_complemento;

    private $uri_complemento;

    private $regimen_antiguo;

    private $numero_autorizacion_documento_origen;

    private $fecha_emision_documento_origen;

    private $motivo_ajuste;

    private $serie_documento_origen;

    private $numero_documento_origen;

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

    public function getRegimenAntiguo() {
        return $this->regimen_antiguo;
    }

    public function setRegimenAntiguo($regimenAntiguo) {
        $this->regimen_antiguo = $regimenAntiguo;
    }

    public function getNumeroAutorizacionDocumentoOrigen() {
        return $this->numero_autorizacion_documento_origen;
    }

    public function setNumeroAutorizacionDocumentoOrigen($numeroAutorizacionDocumentoOrigen) {
        $this->numero_autorizacion_documento_origen = $numeroAutorizacionDocumentoOrigen;
    }

    public function getFechaEmisionDocumentoOrigen() {
        return $this->fecha_emision_documento_origen;
    }

    public function setFechaEmisionDocumentoOrigen($fechaEmisionDocumentoOrigen) {
        $this->fecha_emision_documento_origen = $fechaEmisionDocumentoOrigen;
    }

    public function getMotivoAjuste() {
        return $this->motivo_ajuste;
    }

    public function setMotivoAjuste($motivoAjuste) {
        $this->motivo_ajuste = $motivoAjuste;
    }

    public function getSerieDocumentoOrigen() {
        return $this->serie_documento_origen;
    }

    public function setSerieDocumentoOrigen($serieDocumentoOrigen) {
        $this->serie_documento_origen = $serieDocumentoOrigen;
    }

    public function getNumeroDocumentoOrigen() {
        return $this->numero_documento_origen;
    }

    public function setNumeroDocumentoOrigen($numeroDocumentoOrigen) {
        $this->numero_documento_origen = $numeroDocumentoOrigen;
    }
}
