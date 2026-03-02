
<?php
class Items {
 
    private $numero_linea;
 
    private $bien_o_servicio;

    private $cantidad;

    private $unidad_medida;

    private $descripcion;

    private $precio_unitario;

    private $precio;

    private $descuento;

    private $impuesto_detalle = []; 

    private $total;

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setImpuestosDetalle($impuesto)
    {
        array_push($this->impuesto_detalle,$impuesto);
    }

    public function getImpuestosDetalle()
    {
        return $this->impuesto_detalle;
    }

    public function getNumeroLinea() {
        return $this->numero_linea;
    }


    public function setNumeroLinea($numeroLinea) {
        $this->numero_linea = $numeroLinea;
    }


    public function getBienOServicio() {
        return $this->bien_o_servicio;
    }


    public function setBienOServicio($bienO_servicio) {
        $this->bien_o_servicio = $bienO_servicio;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }


    public function getUnidadMedida() {
        return $this->unidad_medida;
    }

    public function setUnidadMedida($unidadMedida) {
        $this->unidad_medida = $unidadMedida;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getPrecioUnitario() {
        return $this->precio_unitario;
    }

    public function setPrecioUnitario($precioUnitario) {
        $this->precio_unitario = $precioUnitario;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function getDescuento() {
        return $this->descuento;
    }

    public function setDescuento($descuento) {
        $this->descuento = $descuento;
    }
}
