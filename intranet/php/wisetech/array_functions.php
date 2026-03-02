<?php
trait array_functions
{
    /**
     * @param array $parameters lista de parametros que seran validados.
     * @param array $data array en el cual se verificara la existencia de los datos solicitados.
     * @param bool $empty indica si un valor definidio pero vacio es permitido, true  by default
     * Valida la existencia de los datos en un arreglo determinado
     */
    public function validate_parameter_existence($parameters, $data, $empty = true)
    { //verifica que todos los parametros del primer array existan en el segundo
        foreach ($parameters as $parameter) {
            if (!isset($data[$parameter])) {
                $this->report_error(validation_error, $parameter, 'parametro no encontrado');

                return false;
            }
            if (!$empty && $data[$parameter] == '') {
                $this->report_error(validation_error, $parameter, 'parametro encontrado vacio');

                return false;
            }

        }

        return true;
    }

    public function create_subarray($parameters, $data)
    { //devuelve un array con los valores indicador con base en un array mas grande
        $subarray = [];
        foreach ($parameters as $parameter) {
            if (isset($data[$parameter])) {
                $subarray[$parameter] = $data[$parameter];
            }

        }

        return $subarray;
    }

    //sanitizar arreglo con datos.
    public function sanitize_array($DATA)
    { //sanitiza todos los valores del arreglo recidb y devuelve un arreglo limpio.
        foreach ($DATA as $key => $value) {
            $DATA[$key] = self::sanitize_var($value);
        }

        return $DATA;
    }

    public function sanitize_var($var)
    { //santiza una variable recibida y devuelve el valor limpio.
        $var = str_replace('"', "", $var);
        $var = str_replace("'", "", $var);
        $var = str_replace("=", "", $var);
        $var = str_replace("&", "", $var);
        $var = str_replace("|", "", $var);

        // dont show deprecated warnings
        error_reporting(E_ALL ^ E_DEPRECATED);

        return filter_var($var, FILTER_SANITIZE_STRING);
    }
}
