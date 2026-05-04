function activar_tabla_productos() {
    var tabla_productos = document.querySelector('#tabla_productos #tabla_productos_datos, #tabla_productos #tabla_datos');

    if (!tabla_productos || tabla_productos.tagName.toLowerCase() !== 'table') {
        return null;
    }

    if (tabla_productos.id !== 'tabla_productos_datos') {
        tabla_productos.id = 'tabla_productos_datos';
    }

    if ($.fn.dataTable.isDataTable(tabla_productos)) {
        $(tabla_productos).DataTable().destroy();
    }

    return activar_tabla('tabla_productos_datos');
}

function activar_tabla_precios() {
    callback_tabla_precios = function () {
        var tabla_precios = document.querySelector('#tabla_producto_precio #tabla_precios_datos, #tabla_producto_precio #tabla_datos');

        if (!tabla_precios || tabla_precios.tagName.toLowerCase() !== 'table') {
            return;
        }

        if (tabla_precios.id !== 'tabla_precios_datos') {
            tabla_precios.id = 'tabla_precios_datos';
        }

        if ($.fn.dataTable.isDataTable(tabla_precios)) {
            $(tabla_precios).DataTable().destroy();
        }

        tabla = activar_tabla('tabla_precios_datos');
    };

    download_div_content('modelo,idmarca,idtemporada', 'producto_precio', 'tabla_producto_precio', 'tabla_producto_precio', callback_tabla_precios);
}