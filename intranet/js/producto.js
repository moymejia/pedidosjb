function activar_tabla_precios() {
    desactivar_tabla(tabla);
    callback_tabla_precios = function () {
        element('datatableid').value = 'tabla_precios';

        // 4. activar DataTable SOBRE LA NUEVA
        tabla = activar_tabla('tabla_datos');
    }

    download_div_content('modelo,idmarca', 'producto_precio', 'tabla_producto_precio', 'tabla_producto_precio', callback_tabla_precios);
}