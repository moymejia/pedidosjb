
**Manual De Uso: Impresión Y Exportación Global En Datatables**

**1) Objetivo**


    - Permitir imprimir y exportar a Excel todo el bloque del reporte (títulos, subtítulos, párrafos y tablas), no solo una tabla.
    - Mantener el estado actual de las tablas: filtros aplicados, orden y columnas visibles.
    - Excluir elementos de mantenimiento (formulario, título general de pantalla y controles interactivos).

**2) Funciones Nuevas En JavaScript**

Archivo: common.js

    - build_all_datatables_report_content()
        - Parámetros: ninguno.
        - Retorno: un clon del contenido listo para reporte o null si no existe contenedor.
        - Qué hace:
        - Lee contenedor_principal.
        - Toma todas las filas filtradas de tablas activas (incluyendo todas las páginas).
        - Elimina form y elementos con clase header-titulo.
        - Elimina input, select, textarea y button dentro de celdas.
        - Normaliza encabezados de tabla para quitar nodos auxiliares de DataTables.

    - print_all_datatables(dt)
        - Parámetros:
        - dt: opcional, instancia DataTable enviada por botón (no es obligatorio para el proceso).
        - Retorno: sin retorno.
        - Qué hace:
        - Usa build_all_datatables_report_content.
        - Abre ventana de impresión y escribe el contenido listo con CSS de impresión.

    - export_all_datatables(dt)
        - Parámetros:
        - dt: opcional, instancia DataTable enviada por botón.
        - Retorno: sin retorno.
        - Qué hace:
        - Usa build_all_datatables_report_content.
        - Crea contenedor temporal oculto.
        - Llama export_to_xlsx para generar archivo xlsx.
        - Limpia el contenedor temporal.

**3) Funciones Nuevas/Actualizadas En PHP**
Archivo: datatables.php

    - __construct($PARAMETROS = null, $OPTIONS = [])
        - Parámetros:
        - $PARAMETROS: usado para operaciones internas tipo mostrar_tabla, guardar_estado_datatables, etc.
        - $OPTIONS: arreglo de opciones visuales para botones globales.
        - Opciones soportadas en $OPTIONS:
        - print => true para mostrar botón de impresión global.
        - export_all => true para mostrar botón de exportación global.

- addButtonBar()
    - Parámetros: ninguno.
    - Retorno: sin retorno.
    - Qué hace:
    - Si hay opciones activas, agrega botones estilo reportes en la parte superior.
    - Botón imprimir llama print_all_datatables().
    - Botón exportar llama export_all_datatables().





**4) Ejemplo De Uso Basado En Marca**


Pasos:
- Crear instancia con opciones globales:
- $_DATATABLES = new datatables(null, ['print' => true, 'export_all' => true]);

- Definir configuración por tabla:
- $CONFIG_TABLA['buttons'] = true;
- $CONFIG_TABLA['colreorder'] = true;
- $CONFIG_TABLA['responsive'] = true;
- Si deseas también habilitar desde dataset por tabla:
- $CONFIG_TABLA['export_all'] = true;

- Construir contenido del reporte:
- addTitle, addSubTitle, addParagraph (opcionales).
- addTableToReport para cada tabla.

Resultado:
- Se muestran botones globales tipo reporte (arriba a la derecha).
- Imprimir global usa print_all_datatables.
- Exportar global usa export_all_datatables y genera xlsx con export_to_xlsx.

**6) Comportamiento Esperado**
- Sí incluye:
- Títulos/subtítulos/párrafos agregados con datatables.
- Todas las filas filtradas (no solo página actual).
- Orden y visibilidad actual de columnas.

- No incluye:
- Formulario de mantenimiento.
- Título general del mantenimiento.
- Controles DataTables de UI.
- Elementos interactivos incrustados en celdas (input/select/textarea/button).