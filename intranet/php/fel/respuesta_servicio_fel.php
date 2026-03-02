<?php
class RespuestaServicioFel
{

    private $resultado;

    private $fecha;

    private $origen;

    private $descripcion;

    private $descripcion_errores = [];

    private $alertas_infile;

    private $descripcion_alertas_infile = [];

    private $alertas_sat;

    private $descripcion_alertas_sat = [];

    private $cantidad_errores;

    private $control_emision;

    private $informacion_adicional;

    private $uuid;

    private $serie;

    private $numero;

    private $xml_certificado;

    private $info;

    private $json_respuesta;

    public function getResultado()
    {
        return $this->resultado;
    }

    public function setResultado($resultado)
    {
        $this->resultado = $resultado;
    }

    public function getFecha()
    {
        return $this->fecha;
    }

    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    public function getOrigen()
    {
        return $this->origen;
    }

    public function setOrigen($origen)
    {
        $this->origen = $origen;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    public function getDescripcionErrores()
    {
        return $this->descripcion_errores;
    }

    public function setDescripcionErrores($descripcionErrores)
    {

        $this->descripcion_errores = $descripcionErrores;
    }

    public function getAlertasInfile()
    {
        return $this->alertas_infile;
    }

    public function setAlertasInfile($alertasInfile)
    {
        $this->alertas_infile = $alertasInfile;
    }

    public function getDescripcionAlertasInfile()
    {
        return $this->descripcion_alertas_infile;
    }

    public function setDescripcionAlertasInfile($descripcionAlertasInfile)
    {
        $this->descripcion_alertas_infile = $descripcionAlertasInfile;
    }

    public function getAlertasSat()
    {
        return $this->alertas_sat;
    }

    public function setAlertasSat($alertasSat)
    {
        $this->alertas_sat = $alertasSat;
    }

    public function getDescripcionAlertasSat()
    {
        return $this->descripcion_alertas_sat;
    }

    public function setDescripcionAlertasSat($descripcionAlertasSat)
    {

        $this->descripcion_alertas_sat = $descripcionAlertasSat;
    }

    public function getCantidadErrores()
    {
        return $this->cantidad_errores;
    }

    public function setCantidadErrores($cantidadErrores)
    {
        $this->cantidad_errores = $cantidadErrores;
    }

    public function getControlEmision()
    {
        return $this->control_emision;
    }

    public function setControlEmision($controlEmision)
    {
        $this->control_emision = $controlEmision;
    }

    public function getInformacionAdicional()
    {
        return $this->informacion_adicional;
    }

    public function setInformacionAdicional($informacionAdicional)
    {
        $this->informacion_adicional = $informacionAdicional;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    public function getSerie()
    {
        return $this->serie;
    }

    public function setSerie($serie)
    {
        $this->serie = $serie;
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    public function getXmlCertificado()
    {
        return $this->xml_certificado;
    }

    public function setXmlCertificado($xmlCertificado)
    {
        $this->xml_certificado = $xmlCertificado;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getJsonRespuesta()
    {
        return $this->json_respuesta;
    }

    public function setJsonRespuesta($jsonRespuesta)
    {
        $this->json_respuesta = $jsonRespuesta;
    }
}

class DescripcionErrores
{

    private $resultado;

    private $fuente;

    private $categoria;

    private $numeral;

    private $validacion;

    private $mensaje_error;

    public function getResultado()
    {
        return $this->resultado;
    }

    public function setResultado($resultado)
    {
        $this->resultado = $resultado;
    }

    public function getFuente()
    {
        return $this->fuente;
    }

    public function setFuente($fuente)
    {
        $this->fuente = $fuente;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }

    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    }

    public function getNumeral()
    {
        return $this->numeral;
    }

    public function setNumeral($numeral)
    {
        $this->numeral = $numeral;
    }

    public function getValidacion()
    {
        return $this->validacion;
    }

    public function setValidacion($validacion)
    {
        $this->validacion = $validacion;
    }

    public function getMensajeError()
    {
        return $this->mensaje_error;
    }

    public function setMensajeError($mensajeError)
    {
        $this->mensaje_error = $mensajeError;
    }
}

class ControlEmision
{

    private $Saldo;

    private $Creditos;

    public function getSaldo()
    {
        return $this->Saldo;
    }

    public function setSaldo($Saldo)
    {
        $this->Saldo = $Saldo;
    }

    public function getCreditos()
    {
        return $this->Creditos;
    }

    public function setCreditos($Creditos)
    {
        $this->Creditos = $Creditos;
    }
}
