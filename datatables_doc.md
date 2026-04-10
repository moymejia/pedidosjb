# Datatables 2.x

---

## Se agrega
- `Intranet/php/wisetech/datatables.php`
- `Sql/datatables.sql`
  - **Creación de** `_seguridad.datatables`
- **Carpeta** `datatables2`
  - Contienen todos los `*.js` y `*.css` de datatables.js 2.x

---

## Archivos modificados 
- `Intranet/php/wisetech/crud.php`
- `Intranet/pages/main.html/php`
- `Intranet/js/common.js`
  - **Function** `Activar_tabla`

---

## Descripción de uso básico 

Ahora al crear una tabla por ejemplo en **cargar_opcion** de cualquier mantenimiento, se pueden agregar una serie de parámetros opcionales que determinan que características de datatables2 se habilitaran.  

A continuación, se detallan cada uno de ellos cuando están habilitados (**true**) y después daremos un ejemplo de su implementación.

---

# EXPLICACION TEORICA 

---

## **$columnControl**
**Habilita el menu para poder filtar/ordenar.**  

Dicho menu permite filtrar los valores de la columna (y por lo tanto de toda la tabla ) por criterios como:  
- MAYOR QUE  
- MENOR  
- IGUAL  
- CONTIENE  

Este menu aparecera a la izquierda/derecha de cada título de columna (ver imagen)

---

## **$responsive**
Permite que la tabla se adapte automáticamente a pantallas móviles.

---

## **$colReorder**
Permite al usuario arrastrar y soltar las columnas para cambiar su orden.

---

## **$select**
Activa la capacidad de seleccionar filas (individual o múltiple) al hacer clic.  

Además:
- Añade un botón **"Deseleccionar"**
- Configura los botones de exportación para que, si hay filas marcadas, solo se exporte lo seleccionado.

---

## **$buttons**
Muestra la barra de herramientas con botones de exportación:
- Excel  
- PDF  
- etc.

---

## **$paging**
- Si es **false**, la paginacion sera de 10 filas por pagina y no sera posible cambiar.  
- Si es **true**, se permite que el usuario selecciona entre:
  - 10  
  - 25  
  - 50  
  - TODOS  

---

## **$ordering**
Habilita la capacidad global de ordenar los datos al tocar los encabezados.

---

## **$rowGroup**
Esta función organiza la información de la tabla de forma jerárquica, permitiendo visualizar los datos clasificados por una característica común (como Marca, Categoría o Proveedor) en lugar de ver una lista plana.  

Por ejemplo en la siguiente imagen se ordeno por tipo de zapato (bota niña, botin, etc.)

Sin embargo, su implementación es un poco más detallada:

- Por principio si tiene el valor de **false**, entonces simplemente no estará activa dicha agrupación.  
- El segundo caso es que puede tener como valor el **NOMBRE EXACTO** (es realmente importante que sea el nombre exacto de la columna) de una de las columnas de la tabla, y en este caso la agrupación se hará por dicha columna.  

Además:
- A la par de los botones de exportación se agregará un botón que permitirá eliminar la agrupación.

---

## **$tituloTabla**
Esta función permite asignar un título descriptivo a la tabla que se va a imprimir o exportar.  

El valor del título se define a partir del parámetro que se le pase a la función, lo cual facilita identificar el contenido de la tabla en el documento final.  

- En caso de que se envíe el valor **false**, el título del PDF se establecerá automáticamente como **"Listado"** por defecto.

---

## **$fileName**
Esta función permite definir el nombre del archivo que se generará al momento de guardar o exportar la información.  

Por ejemplo:
- Si el valor proporcionado es **"Modelos"**, el archivo se guardará como:  
  **"Listado_de_Modelos_JBR_Innovaciones_y_Servicios"**

- En caso de que se envíe el valor **false**, el archivo se guardará con el nombre por defecto **"Listado"**.

---

# EJEMPLO DE IMPLEMENTACION 

---

## 1º. En el HTML 

Se debe agregar un nuevo input:

```html
<input type="hidden" name="datatableid" id="datatableid" value="tabla_marca">
```

El name/id debe ser "datatableid"  y el value será el nombre con que se guardara el estado de la tabla tanto en el local storage como en la tabla de mysql. 

- **Detección del Input:** El script busca un elemento con el ID `datatableid`. Este input es el que contiene el "nombre deseado" para la tabla (por ejemplo: `tabla_ventas` o `tabla_usuarios`).
- **Validación de Valor:** Si el input existe y tiene un texto válido (no está vacío), el script intenta usar ese valor como el nuevo ID de la tabla.
- **Renombrado:**
  - Si el script encuentra una tabla con el ID genérico (`tabla_datos`), le cambia el ID por el valor del input.
  - Ejemplo: `<table id="tabla_datos">` 
              se convierte automáticamente en 
              `<table id="tabla_productos">`.
- **Confirmación de Existencia:** Si no encuentra la tabla genérica, verifica si ya existe una tabla que use directamente el ID del input.
- **Valor de Respaldo:** Si el input no existe, está vacío, o no se encuentra ninguna tabla coincidente, el script utiliza por defecto el nombre `tabla_datos`.

---

## 2º. En PHP 

En el script que genera la tabla y antes de crear el encabezado de la tabla se deben crear las variables de configuracion

```php
       $columnControl = true;
        $responsive    = true;
        $colReorder    = true;
        $select        = true;
        $buttons       = true;
        $paging        = false;
        $ordering      = true;
        $order         = true;
        $rowGroup      = false;
        $tituloTabla = ‘Listado de modelo: $modelo’
        $fileName = ‘Modelos’
```

Estos se agregarán como valores data-  al encabezado de la tabla, para que sean leídos por el script de js activar_tabla. 

```php
        $data_ = "";
        $data_  = " data-conf-columncontrol='" . ($columnControl ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup='Marca'";
        $data_ .= " data-conf-titulotabla='$tituloTabla' ";
        $data_ .= " data-conf-filename='$fileName' ";
        $data_ .= " data-conf-responsive='"    . ($responsive    ? "true" : "false") . "' ";
        $data_ .= " data-conf-colreorder='"    . ($colReorder    ? "true" : "false") . "' ";
        $data_ .= " data-conf-select='"        . ($select        ? "true" : "false") . "' ";
        $data_ .= " data-conf-buttons='"       . ($buttons       ? "true" : "false") . "' ";
        $data_ .= " data-conf-paging='"        . ($paging        ? "true" : "false") . "' ";
        $data_ .= " data-conf-ordering='"      . ($ordering      ? "true" : "false") . "' ";
        $data_ .= " data-conf-noorder='"       . (!$order        ? "true" : "false") . "' ";
        $data_ .= " data-conf-rowgroup='"       . (!$rowGroup        ? "true" : "false") . "' ";

$tabla_productos = "<table id='tabla_datos'   $data_   class='display nowrap table table-hover   table-bordered datatable' cellspacing='0' width='100%'>
```
