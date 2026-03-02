<?php
define('prefijo', 'pedidosjb');
class mysql
{

    private $mysql_conn;
    private $host     = '10.32.1.83';
    //private $host     = '192.168.1.14';
    private $user     = 'nuevo';
    private $password = 'nuevo';
    private $database;

    /**FUNCIONES PARA MYSQL O MARIADB */
    public function __construct($database = prefijo . '_seguridad')
    { //CREA LA CONEXION
        $this->database   = $database;
        $this->mysql_conn = mysqli_connect($this->host, $this->user, $this->password, $this->database);
        if (mysqli_connect_errno()) {
            utils::report_error(bd_error, ['host' => $this->host, 'user' => $this->user, 'password' => $this->password, 'database' => $this->database], mysqli_connect_error(), false);
            // printf("Error de conexion: %s\n", mysqli_connect_error());
            printf("Error de conexion: a la base de datos");
            exit();
        }
        mysqli_query($this->mysql_conn, "SET NAMES 'utf8'");
        //mysqli_query($this->mysql_conn, "SET time_zone = 'America/Guatemala' ");
    }

    //DEVUELVE RESULTSET DE LA QUERY RECIBIDA.
    public function getresult($query)
    {
        // /*debug*/  echo $query;
        if (!$result = mysqli_query($this->mysql_conn, $query)) {
            utils::report_error(bd_error, $query, mysqli_error($this->mysql_conn));
            //echo "<span style='display:none'>".mysqli_error($this->mysql_conn)."-$query</span>";

            return false;
        }

        return $result;
    }

    //DEVUELVE ROW UNICO (PRIMERO) DE QUERY RECIBIDA.
    public function getrow($query)
    {
        if (!$result = $this->getresult($query)) {
            return false;
        }

        $row = mysqli_fetch_array($result);

        return $row;
    }

    //DEVUELVE VALOR UNICO (CAMPO INDICADO DE PRIMERA FILA) DE QUERY RECIBIDA
    public function getvalue($query, $dato = 0)
    {
        if ($row = $this->getrow($query)) {
            return $row[$dato];
        } else {
            return false;
        }
    }

    //DEVUELVE LA SIGUIENTE FILA DE UN RESULTSET
    public function getrowresult($result)
    {
        $row = mysqli_fetch_assoc($result);

        return $row;
    }

    //EJECTUA QUERY (UPDATE, DELETE) RECIBDA
    public function put($query)
    {
        try {
            if (!mysqli_query($this->mysql_conn, $query)) {
                utils::report_error(bd_error, $query, mysqli_error($this->mysql_conn));
                return false;
            }

            return true;
        } catch (mysqli_sql_exception $e) {
            utils::report_error(bd_error, $query, $e->getMessage());
            
            return false;
        }
    }

    //DEVUELVE EL ULTIM ID INSERTADO
    public function last_id()
    {
        return $this->mysql_conn->insert_id;
    }

    //DEVUELVE LA CANTIDAD DE CAMPOS EN UN RESULTSET
    public function num_fields($result)
    {
        return mysqli_num_fields($result);
    }

    //DEVUELVE LA CANTIDAD DE FILAS EN UN RESULTSET
    public function num_rows($result)
    {
        return mysqli_num_rows($result);
    }

    //DEVUELVE INFORMACION SOBRE UNA COLUMNA ESPECIFICA
    public function fetch_field($result, $index)
    {
        return mysqli_fetch_field_direct($result, $index);
    }

    //DEVUELVE LISTADO DE OPTIONS PARA SELECT CON BASE EN QUERY RECIBIDA, QUERY DEBE INDICAR id y descripcion
    public function getoptions($query)
    {
        $opciones = "<option value=''></option>";
        if (!$result = $this->getresult($query)) {
            return false;
        }

        while ($row = $this->getrowresult($result)) {
            $id          = $row['id'];
            $descripcion = $row['descripcion'];
            $opciones .= "<option value='$id'>$descripcion</option>";
        }

        return $opciones;
    }

    //DEVUELVE LA EXISTENCIA DE UN REGISTRO DE LA TABLA INDICADA, CON BASE EN EL WHERE RECIBIDO.
    public function exists($tabla, $where)
    {
        $query  = "SELECT count(1) existe FROM $tabla  WHERE $where ";
        $existe = $this->getvalue($query, "existe");

        return ($existe > 0) ? true : false;
    }
}
