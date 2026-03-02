
<?php
class AnulacionFel {

    private $fecha_emision_documento_anular;

    private $fecha_hora_anulacion;

    private $id_receptor;

    private $nit_emisor;

    private $motivo_anulacion;

    private $numero_documento_anular;

    public function getFechaEmisionDocumentoAnular() {
        return $this->fecha_emision_documento_anular;
    }

    public function setFechaEmisionDocumentoAnular($fechaEmisionDocumentoAnular) {
        $this->fecha_emision_documento_anular = $fechaEmisionDocumentoAnular;
    }


    public function getFechaHoraAnulacion() {
        return $this->fecha_hora_anulacion;
    }

    public function setFechaHoraAnulacion($fechaHoraAnulacion) {
        $this->fecha_hora_anulacion = $fechaHoraAnulacion;
    }


    public function getIdReceptor() {
        return $this->id_receptor;
    }

    public function setIdReceptor($idReceptor) {
        $this->id_receptor = $idReceptor;
    }


    public function getNitEmisor() {
        return $this->nit_emisor;
    }

    public function setNitEmisor($nitEmisor) {
        $this->nit_emisor = $nitEmisor;
    }


    public function getMotivoAnulacion() {
        return $this->motivo_anulacion;
    }

    public function setMotivoAnulacion($motivoAnulacion) {
        $this->motivo_anulacion = $motivoAnulacion;
    }


    public function getNumeroDocumentoAnular() {
        return $this->numero_documento_anular;
    }

    public function setNumeroDocumentoAnular($numeroDocumentoAnular) {
        $this->numero_documento_anular = $numeroDocumentoAnular;
    }
}