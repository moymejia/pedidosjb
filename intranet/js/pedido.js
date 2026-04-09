(function () {

  // =========================
  // ESTADO
  // =========================
  var state = {
    detalle: [],
    editIndex: -1,
    idproducto: ""
  };

  // =========================
  // DOM
  // =========================
  var $setTallas = document.getElementById("idset_talla");
  var $areaTallas = document.getElementById("areaTallas");
  var $resumenSet = document.getElementById("resumenSet");
  var $alertaProducto = document.getElementById("alertaProducto");

  var $color = document.getElementById("color");
  var $material = document.getElementById("material");
  var $precio = document.getElementById("precio");
  var $modelo = document.getElementById("modelo");
  var $descripcionEstilo = document.getElementById("descripcionEstilo");
  state.idproducto_precio = null;

  function limpiarContenedorImpresion() {
    var contenedor = document.getElementById("div_imprimir_pedido");
    if (contenedor) {
      contenedor.innerHTML = "";
    }
  }

  function imprimirDesdeContenedor(divId) {
    var contenedor = document.getElementById(divId);

    if (!contenedor || !contenedor.innerHTML.trim()) {
      notify_warning('No se pudo generar la vista de impresión.');
      limpiarContenedorImpresion();
      return false;
    }

    var contenido = contenedor.innerHTML;
    var ventanaImpresion = window.open("", "PRINT", "fullscreen=yes");

    if (!ventanaImpresion) {
      limpiarContenedorImpresion();
      notify_warning('El navegador bloqueó la ventana de impresión.');
      return false;
    }

    ventanaImpresion.document.write("<html><head><title>" + document.title + "</title>");
    ventanaImpresion.document.write("</head><body>");
    ventanaImpresion.document.write(contenido);
    ventanaImpresion.document.write("</body></html>");
    ventanaImpresion.document.close();

    limpiarContenedorImpresion();

    ventanaImpresion.focus();
    ventanaImpresion.print();

    return true;
  }

  window.imprimirDesdeContenedor = imprimirDesdeContenedor;

  // =========================
  // RENDER TALLAS
  // =========================

  function cargarDetallePedido(){

    var idpedido = document.getElementById("idpedido").value;
  
    if (!idpedido) return;
  
    callback_detalle = function(resp){
  
      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }
  
      state.detalle = agruparDetalle(resp); 
      renderDetalle(); 
      renderResumen();
    };
  
    upload_action("idpedido=" + idpedido,'pedido_detalle','obtener_por_pedido',callback_detalle);
  }

  window.cargarDetallePedido = cargarDetallePedido;

  function ordenarTallas(tallas) {
    return (tallas || []).slice().sort(function(a, b) {
      var numeroA = String(a.numero || '').trim();
      var numeroB = String(b.numero || '').trim();
      var valorA = parseFloat(numeroA);
      var valorB = parseFloat(numeroB);
      var esNumeroA = numeroA !== '' && !isNaN(valorA);
      var esNumeroB = numeroB !== '' && !isNaN(valorB);

      if (esNumeroA && esNumeroB && valorA !== valorB) {
        return valorA - valorB;
      }

      return numeroA.localeCompare(numeroB, undefined, { numeric: true, sensitivity: 'base' });
    });
  }

  function formatearNumeroTalla(numero) {
    var valor = String(numero === null || numero === undefined ? '' : numero).trim();

    if (valor === '') {
      return '';
    }

    if (/^-?\d+\.0+$/.test(valor)) {
      return valor.replace(/\.0+$/, '');
    }

    return valor;
  }

  function agruparDetalle(data){

    var agrupado = {};
  
    data.forEach(function(row){
  
      var key = [
        row.idset_talla,
        row.codigo || '',
        row.descripcion || '',
        row.color || '',
        row.material || '',
        row.precio_venta || 0,
        row.imagen || ''
      ].join("_");
  
      if (!agrupado[key]) {
        agrupado[key] = {
          idpedido: row.idpedido,
          idproducto: row.idproducto,
          idset_talla: row.idset_talla, 
          set_talla_nombre: row.set_talla || '',
          estilo_codigo: row.codigo,
          estilo_descripcion: row.descripcion,
          color_nombre: row.color,
          marca: row.marca || '',
          imagen: row.imagen || '',
          material: row.material,
          precio: Number(row.precio_venta),
          tallas: [],
          cantidades: {},
          cantidad_total: 0,
          subtotal: 0,
          observaciones: "",
          ids_detalle: []
        };
      }
  
      var yaExiste = agrupado[key].tallas.find(function(t){
        return t.id == row.idtalla;
      });
      
      if (!yaExiste) {
        agrupado[key].tallas.push({
          id: row.idtalla,
          numero: formatearNumeroTalla(row.talla)
        });
      }

      agrupado[key].ids_detalle.push(row.idpedido_detalle);
      agrupado[key].cantidad_total += Number(row.cantidad);
      agrupado[key].cantidades[row.idtalla] = Number(row.cantidad);
      agrupado[key].subtotal += Number(row.subtotal);
  
    });
  
    return Object.values(agrupado).map(function(item) {
      item.tallas = ordenarTallas(item.tallas);
      return item;
    });
  }

  function renderTallas(tallas, nombre) {
    $areaTallas.innerHTML = "";
  
    if (!tallas || !tallas.length) {
      $alertaProducto.className = "alert alert-warning py-2 mb-3";
      $alertaProducto.innerHTML = 'Este set no tiene tallas definidas.';
      return;
    }
  
    $resumenSet.textContent = nombre;
  
    var wrapper = document.createElement("div");
    wrapper.className = "size-grid";
  
    ordenarTallas(tallas).forEach(function (talla) {
  
      var box = document.createElement("div");
      box.className = "size-box";
  
      box.innerHTML =
        '<label>Talla ' + formatearNumeroTalla(talla.numero) + '</label>' +
        '<input type="number" min="0" value="0" ' +
        'id="talla_' + talla.id + '" ' +
        'data-idtalla="' + talla.id + '" ' +
        'class="form-control form-control-sm">';
  
      wrapper.appendChild(box);
  
    });
  
    $areaTallas.appendChild(wrapper);
  
    $alertaProducto.className = "alert alert-success py-2 mb-3";
    $alertaProducto.innerHTML = 'Ingrese cantidades por talla.';
  }

  // =========================
  // CARGAR TALLAS
  // =========================
  function cargarTallas() {

    callback_obtener_tallas = function(resp) {

      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }
      renderTallas(resp.tallas, resp.nombre);
      if (state.editIndex < 0) {
        actualizarPrecioDesdeProductoSet();
      }
    };

    upload_action('idset_talla','set_talla','obtener_grupo',callback_obtener_tallas);
  }

  window.cargarTallas = cargarTallas;

  // =========================
  // BUSCAR ESTILO
  // =========================
  document.getElementById("btnBuscarEstilo").addEventListener("click", function(){

    callback_buscar_estilo = function(resp){

      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }

      state.idproducto = resp.idproducto || "";
      document.getElementById("descripcionEstilo").value = resp.descripcion;
      if (state.idproducto && $setTallas.value) {
        actualizarPrecioDesdeProductoSet();
      }
      notify_success('Producto encontrado');
    };

    upload_action('modelo,idmarca,idtemporada','producto','obtener_modelo',callback_buscar_estilo);

  });

  function getCantidadesTallas() {

    var inputs = $areaTallas.querySelectorAll("[data-idtalla]");
    var cantidades = {};
    var total = 0;
  
    inputs.forEach(function (input) {
  
      var idtalla = input.getAttribute("data-idtalla"); 
      var qty = Number(input.value || 0);
  
      cantidades[idtalla] = qty;
      total += qty;
  
    });
  
    return {
      cantidades: cantidades,
      total: total
    };
  }

  function actualizarPrecioDesdeProductoSet() {
    var idset_talla = $setTallas.value;

    if (!state.idproducto || !idset_talla) {
      state.idproducto_precio = null;
      return;
    }

    callback_precio_set = function(resp) {
      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }

      state.idproducto_precio = resp.idproducto_precio;
      $precio.value = Number(resp.precio || 0).toFixed(2);
    };

    upload_action(
      'idproducto=' + state.idproducto + ',idset_talla=' + idset_talla,
      'producto_precio',
      'obtener_precio_set',
      callback_precio_set
    );
  }

  function eliminarLinea(index){

    var linea = state.detalle[index];
  
    if (!linea) return;
  
    if (!confirm("¿Eliminar esta línea completa?")) return;
  
    var idpedido = document.getElementById("idpedido").value;
  
    var params = [];
  
    params.push("idpedido=" + idpedido);
  
    linea.ids_detalle.forEach(function(id){
      params.push("idpedido_detalle[]=" + id);
    });
  
    callback_eliminar = function(resp){
      notify_success("Producto eliminado correctamente");
      cargarDetallePedido();
    };
  
    upload_action(params.join(','),'pedido_detalle','eliminar',callback_eliminar);
  }

  function editarLinea(index){
    var linea = state.detalle[index];
    if (!linea) return;
    state.editIndex = index;
    state.idproducto = linea.idproducto || "";
    document.getElementById("idset_talla").value = linea.idset_talla;
    $modelo.value = linea.estilo_codigo || "";
    $descripcionEstilo.value = linea.estilo_descripcion || "";
    $color.value = linea.color_nombre || "";
    $material.value = linea.material || "";
    $precio.value = linea.precio;

    callback_obtener_tallas = function(resp){

      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }
    
      $areaTallas.innerHTML = "";
    
      var wrapper = document.createElement("div");
      wrapper.className = "size-grid";
    
      ordenarTallas(resp.tallas).forEach(function(talla){
    
        var idtalla = talla.id;
    
        var box = document.createElement("div");
        box.className = "size-box";
    
        box.innerHTML =
          '<label>Talla ' + formatearNumeroTalla(talla.numero) + '</label>' +
          '<input type="number" min="0" value="'+(linea.cantidades[idtalla] || 0)+'" ' +
          'id="talla_' + idtalla + '" ' +
          'data-idtalla="' + idtalla + '" ' +
          'class="form-control form-control-sm">';
    
        wrapper.appendChild(box);
    
      });
    
      $areaTallas.appendChild(wrapper);
    };
    
    upload_action('idset_talla','set_talla','obtener_grupo',callback_obtener_tallas);
  }

  function agregarProducto(){

    var idpedido = document.getElementById("idpedido").value;
    var idset_talla = document.getElementById("idset_talla").value;
  
    if (!idpedido) {
      notify_warning('Debe guardar el pedido antes de agregar productos');
      return;
    }
  
    if (!idset_talla) {
      notify_warning('Debe seleccionar un set de tallas');
      return;
    }

    if (!$modelo.value.trim()) {
      notify_warning('Debe ingresar un codigo de estilo');
      return;
    }

    if (!state.idproducto) {
      notify_warning('Debe cargar un estilo valido.');
      return;
    }
  
    if (!$color.value.trim()) {
      notify_warning('Debe ingresar un color.');
      return;
    }
  
    if (!$material.value.trim()) {
      notify_warning('Debe ingresar un material');
      return;
    }
  
    var precio = Number($precio.value || 0);
  
    if (precio <= 0) {
      notify_warning('El precio debe ser mayor a 0');
      return;
    }
  
    var tallaInfo = getCantidadesTallas();
  
    if (tallaInfo.total <= 0) {
      notify_warning('Ingrese al menos una cantidad');
      return;
    }

    var params = [];

    params.push("idset_talla=" + idset_talla);
    params.push("idpedido=" + idpedido);
    params.push("idproducto=" + state.idproducto);
    params.push("idproducto_precio=" + (state.idproducto_precio || ''));
    params.push("modelo=" + $modelo.value.trim());
    params.push("descripcionEstilo=" + $descripcionEstilo.value.trim());
    params.push("color=" + $color.value.trim());
    params.push("material=" + $material.value.trim());
    params.push("precio=" + precio);
  
    Object.keys(tallaInfo.cantidades).forEach(function(idtalla){
  
      var cantidad = tallaInfo.cantidades[idtalla];
      params.push("talla_" + idtalla + "=" + cantidad);
    });

    if (state.editIndex >= 0 && state.detalle[state.editIndex]) {
      state.detalle[state.editIndex].ids_detalle.forEach(function(id){
        params.push("idpedido_detalle[]=" + id);
      });
    }
  
    callback_guardar_detalle = function(resp){
  
      notify_success('Producto agregado correctamente.');
      cargarDetallePedido();
      limpiarLinea();
  
    };
  
    var inputImagen = document.getElementById("imagen_producto");
    var tieneImagen = inputImagen && inputImagen.files && inputImagen.files.length > 0;

    if (tieneImagen) {
      upload_file(params.join(','),'imagen_producto','pedido_detalle','guardar',callback_guardar_detalle);
    } else {
      upload_action(params.join(','),'pedido_detalle','guardar',callback_guardar_detalle);
    }

  }

  function safe(val){
    if (val === null || val === undefined || val === 'null') return '';
    return val;
  }

  function renderDetalle(){

    var tbody = document.getElementById("detallePedidoBody");
    tbody.innerHTML = "";
  
    if (!state.detalle.length) {
      tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">No hay productos agregados.</td></tr>';
      return;
    }
  
    state.detalle.forEach(function(linea, index){
  
      var tablaTallas = '<table class="tabla-tallas">';

      tablaTallas += '<tr>';
      linea.tallas.forEach(function(t){
        tablaTallas += '<td style="padding:2px 4px;"><strong>' + formatearNumeroTalla(t.numero) + '</strong></td>';
      });
      tablaTallas += '</tr>';
  
      tablaTallas += '<tr>';
      linea.tallas.forEach(function(t){
        var val = linea.cantidades[t.id] || 0;
        tablaTallas += '<td style="padding:2px 4px; color:'+(val === 0 ? '#ff0000' : '#fff')+'">' + val + '</td>';
      });
      tablaTallas += '</tr>';
  
      tablaTallas += '</table>';
  
      var tr = document.createElement("tr");
      var imgSrc = linea.imagen ? '../' + linea.imagen + '?x=' + Date.now() : 'https://via.placeholder.com/50';
  
      tr.innerHTML =
        '<td><strong>'+safe(linea.estilo_codigo)+'</strong><br><small>'+safe(linea.estilo_descripcion)+'</small></td>' +
        '<td>'+safe(linea.set_talla_nombre)+'</td>'+
        '<td>'+safe(linea.color_nombre)+'</td>' +
        '<td>'+safe(linea.marca)+'</td>' +
        '<td>'+safe(linea.material)+'</td>' +
        '<td class="text-right">Q '+linea.precio.toFixed(2)+'</td>' +
        '<td style="text-align:center;">' + '<img src="' + imgSrc + '" class="shoe-thumb">' + '</td>' +
        '<td>'+tablaTallas+'</td>' +
        '<td class="text-center"><strong>'+linea.cantidad_total+'</strong></td>' +
        '<td class="text-right"><strong>Q '+linea.subtotal.toFixed(2)+'</strong></td>' +
        '<td>' +
        '<button class="btn btn-warning btn-sm btn-editar" data-index="'+index+'">Editar</button> ' +
        '<button class="btn btn-danger btn-sm btn-eliminar" data-index="'+index+'">Eliminar</button>' +
        '</td>';
  
      tbody.appendChild(tr);
  
    });
  
    bindEventosDetalle();
  }

  function bindEventosDetalle(){

    document.querySelectorAll(".btn-eliminar").forEach(function(btn){
  
      btn.addEventListener("click", function(){
  
        var index = Number(btn.getAttribute("data-index"));
  
        eliminarLinea(index);
      });
  
    });

    document.querySelectorAll(".btn-editar").forEach(function(btn){

      btn.addEventListener("click", function(){
    
        var index = Number(btn.getAttribute("data-index"));
        editarLinea(index);
    
      });
    
    });
  }

  function limpiarLinea(){

    clearElements('idset_talla,modelo,descripcionEstilo,color,material');
    $precio.value = "";
  
    var inputs = $areaTallas.querySelectorAll("[data-idtalla]");
    inputs.forEach(function(i){
      i.value = 0;
    });
  
    state.editIndex = -1;
    state.idproducto = "";
    state.idproducto_precio = null;
    document.getElementById("imagen_producto").value = "";
  
  }

  function renderResumen(){

    var totalLineas = state.detalle.length;
    var totalPares = 0;
    var totalMonto = 0;
  
    state.detalle.forEach(function(linea){
  
      totalPares += Number(linea.cantidad_total || 0);
      totalMonto += Number(linea.subtotal || 0);
  
    });
  
    document.getElementById("resumenLineas").textContent = totalLineas;
    document.getElementById("resumenPares").textContent = totalPares;
    document.getElementById("resumenMonto").textContent = "Q " + totalMonto.toLocaleString('en-US', {minimumFractionDigits: 2,maximumFractionDigits: 2
  });
  
  }

  // =========================
  // EVENTOS
  // =========================
  function init() {

    $setTallas.addEventListener("change", cargarTallas);
    $modelo.addEventListener("input", function(){
      state.idproducto = "";
      $descripcionEstilo.value = "";
    });
    document.getElementById("btnAgregarLinea").addEventListener("click", agregarProducto);

    document.getElementById("btnLimpiarLinea")
  .addEventListener("click", limpiarLinea);
  }

  init();

})();
