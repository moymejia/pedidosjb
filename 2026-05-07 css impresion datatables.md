# CSS personalizado para impresión en DataTables

**Fecha:** 2026-05-07

## Problema anterior

El botón **Imprimir** de DataTables tenía todos los estilos CSS embebidos como string dentro de `common.js` (función `activar_tabla`). Cualquier cambio visual requería modificar el JS directamente.

---

## Cambios implementados

### 1. `intranet/js/common.js` — botón print

Se reemplazó el bloque `<style>` inline por la carga de dos archivos CSS externos dentro del callback `customize(win)`:

```js
// CSS genérico — se aplica a todas las tablas
var cssPrint = doc.createElement("link");
cssPrint.rel = "stylesheet";
cssPrint.type = "text/css";
cssPrint.href = "../css/datatables.css?x=" + version;
doc.head.appendChild(cssPrint);

// CSS específico — solo si existe un archivo con el mismo nombre que el id de la tabla
var cssTabla = doc.createElement("link");
cssTabla.rel = "stylesheet";
cssTabla.type = "text/css";
cssTabla.href = "../css/print/" + idtabla + ".css?x=" + version;
doc.head.appendChild(cssTabla);
```

El div de encabezado (título y empresa) también se migró de `style` inline a clases CSS:

```js
$(doc.body).prepend(
    '<div class="dt-print-header">' +
    '<div class="dt-print-title">' + exportTitle + '</div>' +
    '<div class="dt-print-company">' + exportCompany + '</div>' +
    '</div>'
);
```

---

### 2. `intranet/css/datatables.css` — estilos genéricos

Archivo nuevo con todos los estilos que aplican a **cualquier tabla** al imprimir:

- Orientación landscape, márgenes de página
- Fuente base `Arial`, tamaño `8px`
- Tabla 100%, bordes negros, sin espacio entre celdas
- `thead` fijo (repite en cada página impresa)
- Grupos de fila (`dtrg-group`) con fondo blanco y negrita
- Ocultamiento de controles DataTables en vista impresión
- Clases del encabezado:
  - `.dt-print-header` — contenedor centrado
  - `.dt-print-title` — título grande (40px, bold)
  - `.dt-print-company` — nombre empresa (20px, normal)

---

### 3. `intranet/css/print/tabla_marca.css` — estilos específicos de ejemplo

Archivo de ejemplo para la tabla con `id="tabla_marca"`. Sobreescribe selectivamente el CSS genérico:

- Línea divisoria en el encabezado
- Fuente `Georgia/serif` en el título, mayúsculas, espaciado amplio
- Fuente `11px` en celdas (vs `12px` genérico)
- Primera columna en negrita

---

## Cómo agregar estilos para otra tabla

1. Crear el archivo `intranet/css/print/{id_de_la_tabla}.css`
2. Agregar solo las reglas que difieren del genérico
3. No se requiere ningún cambio en JS ni PHP

**Ejemplo:** para una tabla con `id="tabla_inventario"` → crear `intranet/css/print/tabla_inventario.css`

> Si el archivo no existe, el navegador ignora el `link` con 404 sin afectar el funcionamiento.
