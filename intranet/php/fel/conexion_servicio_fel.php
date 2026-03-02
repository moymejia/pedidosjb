<?php
class ConexionServicioFel {

    private $url;


    private $metodo;


    private $usuario;


    private $llave;


    private $identificador;


    private $content_type;

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getMetodo() {
        return $this->metodo;
    }

    public function setMetodo($metodo) {
        $this->metodo = $metodo;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function getLlave() {
        return $this->llave;
    }

    public function setLlave($llave) {
        $this->llave = $llave;
    }

    public function getIdentificador() {
        return $this->identificador;
    }

    public function setIdentificador($identificador) {
        $this->identificador = $identificador;
    }

    public function getContentType() {
        return $this->content_type;
    }

    public function setContentType($contentType) {
        $this->content_type = $contentType;
    }
}
