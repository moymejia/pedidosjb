<?php
class DatosReceptor {

    private $id_receptor;

    private $correo_receptor;

    private $nombre_receptor;

    private $direccion;

    private $codigo_postal;

    private $municipio;

    private $departamento;

    private $pais;

    private $tipo_especial;

    public function getIdReceptor() {
        return $this->id_receptor;
    }

    public function setIdReceptor($idReceptor) {
        $this->id_receptor = $idReceptor;
    }

    public function getCorreoReceptor() {
        return $this->correo_receptor;
    }

    public function setCorreoReceptor($correoReceptor) {
        $this->correo_receptor = $correoReceptor;
    }

    public function getNombreReceptor() {
        return $this->nombre_receptor;
    }

    public function setNombreReceptor($nombreReceptor) {
        $this->nombre_receptor = $nombreReceptor;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    public function getCodigoPostal() {
        return $this->codigo_postal;
    }

    public function setCodigoPostal($codigoPostal) {
        $this->codigo_postal = $codigoPostal;
    }

    public function getMunicipio() {
        return $this->municipio;
    }

    public function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    public function getDepartamento() {
        return $this->departamento;
    }

    public function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }

    public function getPais() {
        return $this->pais;
    }

    public function setPais($pais) {
        $this->pais = $pais;
    }

    public function getTipoEspecial() {
        return $this->tipo_especial;
    }

    public function setTipoEspecial($tipoEspecial) {
        $this->tipo_especial = $tipoEspecial;
    }
}
