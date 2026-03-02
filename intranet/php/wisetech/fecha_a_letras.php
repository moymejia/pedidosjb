<?php
trait fecha_a_letras{

    public function convertir_fecha_a_letras($fecha){
        $date = strtotime($fecha);
        $dia=date("l",$date); 
		if ($dia=="Monday") $dia="Lunes"; 
		if ($dia=="Tuesday") $dia="Martes"; 
		if ($dia=="Wednesday") $dia="Miercoles"; 
		if ($dia=="Thursday") $dia="Jueves"; 
		if ($dia=="Friday") $dia="Viernes"; 
		if ($dia=="Saturday") $dia="Sabado"; 
		if ($dia=="Sunday") $dia="Domingo"; 
		$dia2=date("d"); 
		$mes=date("F"); 
		if ($mes=="January") $mes="Enero"; 
		if ($mes=="February") $mes="Febrero"; 
		if ($mes=="March") $mes="Marzo"; 
		if ($mes=="April") $mes="Abril"; 
		if ($mes=="May") $mes="Mayo"; 
		if ($mes=="June") $mes="Junio"; 
		if ($mes=="July") $mes="Julio"; 
		if ($mes=="August") $mes="Agosto"; 
		if ($mes=="September") $mes="Septiembre"; 
		if ($mes=="October") $mes="Octubre"; 
		if ($mes=="November") $mes="Noviembre"; 
		if ($mes=="December") $mes="Diciembre"; 
        $ano=date("Y");
        return "el día $dia $dia2 del mes de $mes del año $ano";
    }

    public function fecha_actual_en_letras(){
        $date = date('Y-m-d');
        return self::convertir_fecha_a_letras($date);
    }

}
?>