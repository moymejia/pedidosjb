proyecto web empresarial. 
comunicacion entre el navegador y el servidor se hace a traves de las funciones de common.js. 
  - download_select_options(fields, table, operation, destiny, callback = null, callback_error = null)
  - download_input_value(fields, table, operation, destiny, callback = null, callback_error = null)
  - download_div_content(fields, table, operation, destiny, callback = null, do_activate_switch = true, callback_error = null)
  - las tres reciben ids de campos separados por coma,la clase a la que se enviar la peticion, la operacion que se debe ejecutar y funciones callback a ejecutarse en exito o error.  
pages/main.html.php es la pagina principal, cada opcion y todo lo demas se cargan por ajax desde esa pagina


ARCHIVOS IMPORTANTES:
- intranet/js/common.js
- intrenet/pages/main.html.php 
- intrenat/php/WiseTech/ (archivos de librerias de sistema)
- intrenat/php/WiseTech/crud.php (recibe y direcciona todas las llamdas de common.js)
- intrenat/php/entities/ (clases)


REGLAS 
- cada tabla debe tener su archivo php con el mismo nombre de la tabla y el mismo nombre de clase. 
- para usar otra clase se incluye al archivo en el inicio, se usa new y se instancia la clas. 
- nombre de clases instanciadas inician con $_NOMBRECLASE
- varibales que son arrays se nombran en mayusculas $ARRAY 
- variables en minusculas y guion bajo $nombre_variable 
- no deben aparecer inner join en codigo, se hacen vistas en la base de datos.
  - crear un archivo con la fecha y una breve descripcion para que el usuario lo ejecute.
- se crean archivos .sql en la carpeta /sql para ir agregarndo scritps de sql que corresponden, deben ir nombrados con la fecha. 
- toda operacion debe llevar un permiso que se valida con la libreria security. 
- toda operacion debe dejar registro de bitacora. 
- toda validacion y deteccion de error se debe registrar a traves de utils::report_error
  

opciones: 
en common.js cargar opcion. 
si se necesita js se crea un archivo con el mismo nombre y en el formulario debe ir un input hiddden con id jsid (ver funcion mostrar_opcion en common.js)

base de datos:
- la estructura completa de la base de datos esta en el archivo /sql/estructura/  (un archivo pr esquema)