
<?php
class AbonosFacturaCambiaria {
    private $numero_abono;

    private $fecha_vencimiento;

    private $monto_abono;

    public function getNumeroAbono() {
        return $this->numero_abono;
    }

    public function setNumeroAbono($numeroAbono) {
        $this->numero_abono = $numeroAbono;
    }

    public function getFechaVencimiento() {
        return $this->fecha_vencimiento;
    }

    public function setFechaVencimiento($fechaVencimiento) {
        $this->fecha_vencimiento = $fechaVencimiento;
    }

    public function getMontoAbono() {
        return $this->monto_abono;
    }

    public function setMontoAbono($montoAbono) {
        $this->monto_abono = $montoAbono;
    }

}
class ComplementoCambiaria  {

    private $id_complemento;


    private $nombre_complemento;


    private $uri_complemento;

    private $abono = [];

    public function setAbono($abono)
    {
        array_push($this->abono,$abono);
    }

    public function getAbono()
    {
        return $this->abono;
    }
  

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

  
}
