<?php
class ComplementoExportacion {

    private $id_complemento;

    private $nombre_complemento;

    private $uri_complemento;

    private $nombre_consignatario_o_destinatario;

    private $direccion_consignatario_o_destinatario;

    private $codigo_consignatario_o_destinatario;

    private $nombre_comprador;

    private $direccion_comprador;

    private $codigo_comprador;

    private $otra_referencia;

    private $incoterm;

    private $nombre_exportador;

    private $codigo_exportador;

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

    public function getNombreConsignatarioODestinatario() {
        return $this->nombre_consignatario_o_destinatario;
    }

    public function setNombreConsignatarioODestinatario($nombreConsignatarioO_destinatario) {
        $this->nombre_consignatario_o_destinatario = $nombreConsignatarioO_destinatario;
    }



    public function getDireccionConsignatarioODestinatario() {
        return $this->direccion_consignatario_o_destinatario;
    }

    public function setDireccionConsignatarioODestinatario($direccionConsignatarioO_destinatario) {
        $this->direccion_consignatario_o_destinatario = $direccionConsignatarioO_destinatario;
    }

    public function getCodigoConsignatarioODestinatario() {
        return $this->codigo_consignatario_o_destinatario;
    }

    public function setCodigoConsignatarioODestinatario($codigoConsignatarioO_destinatario) {
        $this->codigo_consignatario_o_destinatario = $codigoConsignatarioO_destinatario;
    }

    public function getNombreComprador() {
        return $this->nombre_comprador;
    }

    public function setNombreComprador($nombreComprador) {
        $this->nombre_comprador = $nombreComprador;
    }

    public function getDireccionComprador() {
        return $this->direccion_comprador;
    }

    public function setDireccionComprador($direccionComprador) {
        $this->direccion_comprador = $direccionComprador;
    }

    public function getCodigoComprador() {
        return $this->codigo_comprador;
    }

    public function setCodigoComprador($codigoComprador) {
        $this->codigo_comprador = $codigoComprador;
    }

    public function getOtraReferencia() {
        return $this->otra_referencia;
    }

    public function setOtraReferencia($otraReferencia) {
        $this->otra_referencia = $otraReferencia;
    }

    public function getIncoterm() {
        return $this->incoterm;
    }

    public function setIncoterm($incoterm) {
        $this->incoterm = $incoterm;
    }

    public function getNombreExportador() {
        return $this->nombre_exportador;
    }

    public function setNombreExportador($nombreExportador) {
        $this->nombre_exportador = $nombreExportador;
    }

    public function getCodigoExportador() {
        return $this->codigo_exportador;
    }

    public function setCodigoExportador($codigoExportador) {
        $this->codigo_exportador = $codigoExportador;
    }
}
