(function () {

  // =========================
  // ESTADO
  // =========================
  var state = {
    estiloActual: null,
    colorActual: null,
    detalle: [],
    editIndex: -1,
    idproducto_precio: null
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

  function agruparDetalle(data){

    var agrupado = {};
  
    data.forEach(function(row){
  
      var key = row.idproducto_precio + "_" + row.idset_talla;
  
      if (!agrupado[key]) {
        agrupado[key] = {
          idpedido: row.idpedido,
          idpedido_detalle: row.idpedido_detalle,
          idproducto: row.idproducto,
          idproducto_precio: row.idproducto_precio,
          idset_talla: row.idset_talla, 
          set_talla_nombre: row.set_talla || '',
          estilo_codigo: row.codigo,
          estilo_descripcion: row.descripcion,
          idcolor: row.idcolor,
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
          numero: row.talla
        });
      }

      agrupado[key].ids_detalle.push(row.idpedido_detalle);
      agrupado[key].cantidad_total += Number(row.cantidad);
      agrupado[key].cantidades[row.idtalla] = Number(row.cantidad);
      agrupado[key].subtotal += Number(row.subtotal);
  
    });
  
    return Object.values(agrupado);
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
  
    tallas.forEach(function (talla) {
  
      var box = document.createElement("div");
      box.className = "size-box";
  
      box.innerHTML =
        '<label>Talla ' + talla.numero + '</label>' +
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
    };

    upload_action('idset_talla','set_talla','obtener_grupo',callback_obtener_tallas);
  }

  window.cargarTallas = cargarTallas;

  // =========================
  // CAMBIO DE COLOR
  // =========================
  function onColorChange() {

    if (!state.estiloActual) return;
  
    var selected = $color.value.trim().toUpperCase();
  
    var color = state.estiloActual.colores.find(function (c) {
      return c.id.trim().toUpperCase() === selected;
    });
  
    state.colorActual = color;
  
    $material.innerHTML = "<option value=''>Seleccione...</option>";
    $precio.value = "";
  
    // llenar materiales
    color.materiales.forEach(function(mat){
      var opt = document.createElement("option");
      opt.value = mat.nombre;
      opt.textContent = mat.nombre;
      opt.setAttribute("data-precio", mat.precio); 
      opt.setAttribute("data-idproducto_precio", mat.idproducto_precio);
      $material.appendChild(opt);
    });
  
    $material.value = color.material_default;
    onMaterialChange(); 
  }

  // =========================
  // BUSCAR ESTILO
  // =========================
  document.getElementById("btnBuscarEstilo").addEventListener("click", function(){

    callback_buscar_estilo = function(resp){

      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }

      state.estiloActual = resp;

      document.getElementById("descripcionEstilo").value = resp.descripcion;

      // limpiar selects
      $color.innerHTML = "<option value=''>Seleccione...</option>";
      $material.innerHTML = "<option value=''>Seleccione...</option>";
      $precio.value = "";

      // llenar colores
      resp.colores.forEach(function(c){
        $color.innerHTML += "<option value='"+c.id+"'>"+c.nombre+"</option>";
      });

    };

    upload_action('modelo,idmarca,idtemporada','producto','obtener_modelo',callback_buscar_estilo);

  });

  function onMaterialChange(){

    var selected = $material.options[$material.selectedIndex];
  
    if (!selected) return;
  
    var precio = selected.getAttribute("data-precio");
    var idproducto_precio = selected.getAttribute("data-idproducto_precio");

    $precio.value = precio ? parseFloat(precio) : 0;

    state.idproducto_precio = idproducto_precio;
  }

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
    state.editIndex = index;

    
    disableElements('modelo,idset_talla,color,material');
    document.getElementById("idset_talla").value = linea.idset_talla;
    cargarTallas();
  
    // 1. modelo
    document.getElementById("modelo").value = linea.estilo_codigo;
    
  
    callback_buscar_estilo = function(resp){
  
      if (typeof resp === "string") {
        resp = JSON.parse(resp);
      }
  
      state.estiloActual = resp;
  
      document.getElementById("descripcionEstilo").value = resp.descripcion;
  
      // limpiar
      $color.innerHTML = "<option value=''>Seleccione...</option>";
      $material.innerHTML = "<option value=''>Seleccione...</option>";
      $precio.value = "";
  
      // llenar colores
      resp.colores.forEach(function(c){
        $color.innerHTML += "<option value='"+c.id+"'>"+c.nombre+"</option>";
      });

      var colorNombre = (linea.color_nombre || '').toUpperCase();

      var colorEncontrado = resp.colores.find(function(c){
        return (
          c.id == linea.idcolor || 
          c.nombre.toUpperCase() == colorNombre
        );
      });
      
      if (colorEncontrado) {
        $color.value = colorEncontrado.id;
        onColorChange();
      
        // ahora sí material
        setTimeout(function(){

          var encontrado = Array.from($material.options).find(function(opt){
            return Number(opt.getAttribute("data-idproducto_precio")) === Number(linea.idproducto_precio);
          });
        
          if (encontrado) {
            $material.value = encontrado.value;
            onMaterialChange(); 
          }
        
        }, 50);
      }

      $precio.value = linea.precio;
      $areaTallas.innerHTML = "";
  
      var wrapper = document.createElement("div");
      wrapper.className = "size-grid";
  
      callback_obtener_tallas = function(resp){

        if (typeof resp === "string") {
          resp = JSON.parse(resp);
        }
      
        $areaTallas.innerHTML = "";
      
        var wrapper = document.createElement("div");
        wrapper.className = "size-grid";
      
        resp.tallas.forEach(function(talla){
      
          var idtalla = talla.id;
      
          var box = document.createElement("div");
          box.className = "size-box";
      
          box.innerHTML =
            '<label>Talla ' + talla.numero + '</label>' +
            '<input type="number" min="0" value="'+(linea.cantidades[idtalla] || 0)+'" ' +
            'id="talla_' + idtalla + '" ' +
            'data-idtalla="' + idtalla + '" ' +
            'class="form-control form-control-sm">';
      
          wrapper.appendChild(box);
      
        });
      
        $areaTallas.appendChild(wrapper);
      };
      
      upload_action('idset_talla','set_talla','obtener_grupo',callback_obtener_tallas);
  
    };
  
    upload_action('modelo,idmarca,idtemporada','producto','obtener_modelo',callback_buscar_estilo);
  }

  function agregarProducto(){

    var idpedido = document.getElementById("idpedido").value;
    var idset_talla = document.getElementById("idset_talla").value;
  
    if (!idpedido) {
      notify_warning('Debe guardar el pedido antes de agregar productos');
      return;
    }
  
    if (!state.estiloActual) {
      notify_warning('Debe cargar un estilo');
      return;
    }
  
    if (!$color.value) {
      notify_warning('Debe seleccionar un color.');
      return;
    }
  
    if (!$material.value) {
      notify_warning('Seleccione un material');
      return;
    }
  
    var precio = Number($precio.value || 0);
  
    if (precio <= 0) {
      notify_warning('Precio inválido');
      return;
    }
  
    var tallaInfo = getCantidadesTallas();
  
    if (tallaInfo.total <= 0) {
      notify_warning('Ingrese al menos una cantidad');
      return;
    }

    var inputImagen = document.getElementById("imagen_producto");

    if (!inputImagen || !inputImagen.files || inputImagen.files.length === 0) {
      notify_warning('Debe seleccionar una imagen del producto');
      return;
    }

    var color = state.colorActual;
    var params = [];

    params.push("idset_talla=" + idset_talla);
    params.push("idpedido_detalle="+state.idpedido_detalle); 
    params.push("idpedido=" + idpedido);
    params.push("idproducto=" + color.idproducto);
    params.push("idproducto_precio=" + state.idproducto_precio);
    params.push("precio=" + precio);
    params.push("idcolor=" + color.id);
  
    Object.keys(tallaInfo.cantidades).forEach(function(idtalla){
  
    var cantidad = tallaInfo.cantidades[idtalla];
    params.push("talla_" + idtalla + "=" + cantidad);

  
    });
  
    callback_guardar_detalle = function(resp){
  
      notify_success('Producto agregado correctamente.');
      cargarDetallePedido();
      limpiarLinea();
  
    };

    upload_file(params.join(','),'imagen_producto','pedido_detalle','guardar',callback_guardar_detalle);
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
        tablaTallas += '<td style="padding:2px 4px;"><strong>' + t.numero + '</strong></td>';
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

    clearElements('idset_talla,modelo,color,material');
    enableElements('modelo,idset_talla,color,material');
  
    $color.innerHTML = "<option value=''>Seleccione...</option>";
    $material.innerHTML = "<option value=''>Seleccione...</option>";
    $precio.value = "";
  
    var inputs = $areaTallas.querySelectorAll("[data-idtalla]");
    inputs.forEach(function(i){
      i.value = 0;
    });
  
    state.estiloActual = null;
    state.colorActual = null;
  
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
    $color.addEventListener("change", onColorChange);
    $material.addEventListener("change", onMaterialChange);
    document.getElementById("btnAgregarLinea").addEventListener("click", agregarProducto);

    document.getElementById("btnLimpiarLinea")
  .addEventListener("click", limpiarLinea);
  }

  init();

})();