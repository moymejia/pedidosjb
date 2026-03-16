<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    die();
}
require_once '../php/wisetech/mysql.php';
$mysql              = new mysql(prefijo . '_seguridad');
$encabezado_sistema = $mysql->getvalue("SELECT valor FROM configuracion WHERE clave = 'encabezado_sistema' ");
$footer_sistema     = $mysql->getvalue("SELECT valor FROM configuracion WHERE clave = 'footer_sistema' ");

$usuario = $_SESSION['usuario'];
$nombre  = $_SESSION['usuario_nombre'];
/*
$result             = $mysql->getresult("SELECT abreviatura, usuario_ubicacion.idubicacion
FROM  aseguades_seguridad.usuario_ubicacion
INNER JOIN legans_inventario.ubicacion ON ubicacion.idubicacion = usuario_ubicacion.idubicacion
WHERE usuario = '$usuario' AND indUbicacion_principal = 'SI'");
$row         = $mysql->getrowresult($result);
$ubicacion   = $row['abreviatura'];
$idubicacion = $row['idubicacion'];
 */

// require_once '../php/entities/actividad.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Pedidos jb</title>
    <!-- Bootstrap Core CSS -->
    <link href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/plugins/toast-master/css/jquery.toast.css" rel="stylesheet">
    <link href="../assets/plugins/bootstrap-switch/bootstrap-switch.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">

    <link href="../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- You can change the theme colors from here -->
    <link href="css/pages/floating-label.css" rel="stylesheet">
    <link href="css/pages/tab-page.css" rel="stylesheet">
    <link href="css/colors/default-dark.css" id="theme" rel="stylesheet">
    <link href="../css/common.css" rel="stylesheet">

    <script src="../js/chart.js?x=<?php echo date('YmdHis'); ?>"></script>
    <script src="../js/main.js?x=<?php echo date('YmdHis'); ?>"></script>
    <script src="../js/common.js?x=<?php echo date('YmdHis'); ?>""></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="fix-header card-no-border fix-sidebar" onload="
    const params = new URLSearchParams(window.location.search);

    if (params.has('idopcion')) {
        mostrar_opcion(
            params.get('idopcion'),
            params.get('opcion'),
            params.get('menu'),
            restore_data_local_storage
        );
    }
" >
    <div>
        <!-- EL TOKEN -->
    </div>

    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <input type="hidden" name="idactividad_" id="idactividad_">
    <div class="preloader">
        <div class="loader">
            <div class="loader__figure"></div>
            <p class="loader__label">WiseTech.Solutions</p>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">

                <div class="navbar-collapse">

                    <!-- BOTON MENU + ENCABEZADO -->
                    <ul class="navbar-nav mr-auto">

                        <li class="nav-item">
                            <a class="nav-link nav-toggler hidden-md-up waves-effect hidden-md-up waves-dark" href="javascript:void(0)">
                                <i class="ti-menu"></i>
                            </a>
                        </li>

                        <li class="nav-item hidden-sm-down"></li>

                        <span id="encabezado_sistema">
                            <?php echo $encabezado_sistema ?>
                        </span>

                    </ul>


                    <!-- MENU -->
                    <aside class="left-sidebar">

                        <nav class="sidebar-nav">

                            <ul id="sidebarnav">

                            <li class="nav-item">
                            <a class="nav-link nav-toggler hidden-md-up waves-effect hidden-md-up waves-dark" href="javascript:void(0)">
                                <i class="ti-menu"></i>
                            </a>
                        </li>


                                <li class="user-profile">
                                    <a class="has-arrow waves-effect waves-dark" href="">
                                        <i class="mdi mdi-account-circle"></i>
                                        <span class="hide-menu">
                                            <?php echo $nombre ?>
                                        </span>
                                    </a>
                                </li>

                                <input type="hidden" id="idopcion_actual">

                                <?php

$idrol = $mysql->getvalue("SELECT idrol FROM usuario WHERE usuario = '$usuario'", 'idrol');

$result = $mysql->getresult("SELECT idmenu, menu, icono FROM view_permisos WHERE idrol = $idrol GROUP BY idmenu ORDER BY orden_menu");

while ($row = $mysql->getrowresult($result)) {

    $menu       = $row['menu'];
    $icono_menu = $row['icono'];
    $idmenu     = $row['idmenu'];

    $cantidad_opciones = $mysql->getvalue("SELECT COUNT(DISTINCT idopcion) FROM view_permisos WHERE idrol = $idrol AND idmenu = $idmenu");

    if ($cantidad_opciones > 0) {

        echo "
                                            <li>
                                                <a class='has-arrow waves-effect waves-dark' href='#'>
                                                    <i class='$icono_menu'></i>
                                                    <span class='hide-menu'>$menu</span>
                                                </a>

                                                <ul class='collapse'>
                                            ";

        $result_opciones = $mysql->getresult("SELECT idopcion, opcion FROM view_permisos WHERE idrol = '$idrol' AND idmenu = '$idmenu' GROUP BY idopcion ORDER BY orden_opcion
                                            ");

        while ($row_opcion = $mysql->getrowresult($result_opciones)) {

            $opcion   = $row_opcion['opcion'];
            $idopcion = $row_opcion['idopcion'];

            echo "
                                                    <li>
                                                        <a href='#'
                                                        onclick=\"mostrar_opcion($idopcion,'$opcion','$menu');\">
                                                            $opcion
                                                        </a>
                                                    </li>
                                                ";
        }

        echo "
                                                </ul>
                                            </li>
                                            ";
    }
}
?>

                            </ul>

                        </nav>

                    </aside>


                    <!-- BOTON SALIR -->
                    <ul class="navbar-nav my-lg-0">
                        <li>
                            <a href="../" class="m-r-10">
                                <i class="ti-user"></i> Salir
                            </a>
                        </li>
                    </ul>

                </div>

            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->

        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid" id='div_principal'>
                <!-- ============================================================== -->
                <!-- Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <div class="row page-titles m-b-5">
                    <div class="col-md-5 align-self-center">
                        <h1 class="text-themecolor" id="titulo_actual"></h1>
                    </div>
                    <div class="col-md-7 align-self-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item" id="menu_actual">Inicio</li>
                            <li class="breadcrumb-item" id="opcion_actual">Paginas</li>
                            <li class="breadcrumb-item active" id="detalle_actual">Actual</li>
                        </ol>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Bread crumb and right sidebar toggle -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-body" id="contenedor_principal" style="min-height: 600px; border: 3px solid var(--border-card); padding:20px; background-color: var(--brg-card)">
                          <!-- Pantalla de Toma de Pedidos -->
<div class="container-fluid px-0" id="pedidoApp">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-secondary border-bottom">
      <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div>
          <h4 class="mb-0">Toma de pedido</h4>
          <small class="text-muted">Ingreso de pedido por estilo, color y corrida de tallas</small>
        </div>
        <div class="mt-2 mt-md-0">
          <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="btnLimpiarPedido">Limpiar</button>
          <button type="button" class="btn btn-success btn-sm" id="btnGuardarPedido">Guardar pedido</button>
        </div>
      </div>
    </div>

    <div class="card-body">
      <!-- Encabezado -->
      <div class="card mb-3 border">
        <div class="card-header bg-dark">
          <strong>Datos generales</strong>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Cliente</label>
              <select class="form-control" id="cliente">
                <option value="">Seleccione...</option>
              </select>
            </div>

            <div class="form-group col-md-2">
              <label>Marca</label>
              <select class="form-control" id="marca">
                <option value="">Seleccione...</option>
              </select>
            </div>

            <div class="form-group col-md-2">
              <label>Entrega desde</label>
              <input type="date" class="form-control" id="fechaDesde">
            </div>

            <div class="form-group col-md-2">
              <label>Entrega hasta</label>
              <input type="date" class="form-control" id="fechaHasta">
            </div>

            <div class="form-group col-md-2">
              <label>Temporada</label>
              <select class="form-control" id="temporada">
                <option value="">Seleccione...</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-3">
              <label>Set de tallas</label>
              <select class="form-control" id="setTallas"></select>
              <small class="form-text text-muted" id="setTallasHelp">Seleccione un set para habilitar el ingreso por talla.</small>
            </div>

            <div class="form-group col-md-9">
              <label>Observaciones generales</label>
              <textarea class="form-control" id="observacionesPedido" rows="2" placeholder="Observaciones del pedido..."></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Resumen -->
      <div class="row mb-3">
        <div class="col-md-3 mb-2">
          <div class="border rounded p-3 h-100 bg-dark">
            <div class="small text-muted">Total líneas</div>
            <div class="h4 mb-0" id="resumenLineas">0</div>
          </div>
        </div>
        <div class="col-md-3 mb-2">
          <div class="border rounded p-3 h-100 bg-dark">
            <div class="small text-muted">Cantidad de pares</div>
            <div class="h4 mb-0" id="resumenPares">0</div>
          </div>
        </div>
        <div class="col-md-3 mb-2">
          <div class="border rounded p-3 h-100 bg-dark">
            <div class="small text-muted">Monto total</div>
            <div class="h4 mb-0" id="resumenMonto">Q 0.00</div>
          </div>
        </div>
        <div class="col-md-3 mb-2">
          <div class="border rounded p-3 h-100 bg-dark">
            <div class="small text-muted">Set activo</div>
            <div class="h6 mb-0 pt-2" id="resumenSet">Sin seleccionar</div>
          </div>
        </div>
      </div>

      <!-- Ingreso de producto -->
      <div class="card border mb-3">
        <div class="card-header bg-dark d-flex justify-content-between align-items-center">
          <strong>Agregar producto</strong>
          <small class="text-muted">Ingrese el estilo y complete la corrida</small>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-2">
              <label>Código de estilo</label>
              <div class="input-group">
                <input type="text" class="form-control" id="codigoEstilo" placeholder="Ej: ST-1001">
                <div class="input-group-append">
                  <button class="btn btn-primary" type="button" id="btnBuscarEstilo">Cargar</button>
                </div>
              </div>
            </div>

            <div class="form-group col-md-2">
              <label>Descripción estilo</label>
              <input type="text" class="form-control bg-secondary" id="descripcionEstilo" readonly>
            </div>

            <div class="form-group col-md-2">
              <label>Color</label>
              <select class="form-control" id="color"></select>
            </div>

            <div class="form-group col-md-2">
              <label>Material</label>
              <select class="form-control" id="material"></select>
            </div>

            <div class="form-group col-md-2">
              <label>Precio</label>
              <input type="number" class="form-control text-right" id="precio" step="0.01" min="0">
            </div>

            <div class="form-group col-md-2">
              <label>Observaciones línea</label>
              <input type="text" class="form-control" id="observacionesLinea" placeholder="Opcional">
            </div>
          </div>

          <div class="alert alert-info py-2 mb-3" id="alertaProducto">
            Seleccione un <strong>set de tallas</strong> y cargue un <strong>estilo</strong> para habilitar el ingreso de cantidades.
          </div>

          <div id="areaTallas" class="mb-3"></div>

          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-outline-secondary mr-2" id="btnLimpiarLinea">Limpiar línea</button>
            <button type="button" class="btn btn-primary" id="btnAgregarLinea">Agregar producto</button>
          </div>
        </div>
      </div>

      <!-- Detalle -->
      <div class="card border">
        <div class="card-header bg-dark">
          <strong>Detalle del pedido</strong>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0 align-middle">
              <thead class="thead-light">
                <tr>
                  <th style="min-width:110px;">Estilo</th>
                  <th style="min-width:130px;">Color</th>
                  <th style="min-width:130px;">Color base</th>
                  <th style="min-width:120px;">Marca</th>
                  <th style="min-width:120px;">Material</th>
                  <th style="min-width:120px;" class="text-right">Precio</th>
                  <th style="min-width:110px;">Imagen</th>
                  <th style="min-width:190px;">Tallas</th>
                  <th style="min-width:90px;" class="text-center">Cantidad</th>
                  <th style="min-width:140px;" class="text-right">Subtotal</th>
                  <th style="min-width:180px;">Observaciones</th>
                  <th style="min-width:140px;">Acciones</th>
                </tr>
              </thead>
              <tbody id="detallePedidoBody">
                <tr id="filaSinDatos">
                  <td colspan="12" class="text-center text-muted py-4">No hay productos agregados.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- JSON demo -->
      <div class="mt-3 d-none" id="jsonWrap">
        <label class="font-weight-bold">Payload JSON de ejemplo</label>
        <textarea id="jsonSalida" class="form-control" rows="12" readonly></textarea>
      </div>
    </div>
  </div>
</div>

<style>
  #pedidoApp .card-header strong {
    font-weight: 600;
  }

  #pedidoApp .table td,
  #pedidoApp .table th {
    vertical-align: top;
  }

  #pedidoApp .shoe-thumb {
    width: 64px;
    height: 48px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
  }

  #pedidoApp .size-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(85px, 1fr));
    grid-gap: 10px;
  }

  #pedidoApp .size-box {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    transition: box-shadow .15s ease, border-color .15s ease;
  }

  #pedidoApp .size-box:hover {
    border-color: #adb5bd;
    box-shadow: 0 0 0 0.1rem rgba(0,123,255,.08);
  }

  #pedidoApp .size-box label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 13px;
  }

  #pedidoApp .size-box input {
    text-align: center;
  }

  #pedidoApp .sizes-compact {
    line-height: 1.35;
  }

  #pedidoApp .sizes-compact .sizes-labels,
  #pedidoApp .sizes-compact .sizes-values {
    white-space: nowrap;
  }

  #pedidoApp .readonly-like {
    background: #fff;
  }

  #pedidoApp .sticky-summary {
    position: sticky;
    top: 0;
    z-index: 10;
  }
</style>

<script>
(function () {
  // =========================
  // Datos demo
  // =========================
  var dataDemo = {
    clientes: [
      { id: 1, nombre: "Calzado San José" },
      { id: 2, nombre: "Distribuidora El Centro" },
      { id: 3, nombre: "Boutique Elegance" }
    ],
    marcas: [
      { id: 1, nombre: "Pretty" },
      { id: 2, nombre: "Urban Step" },
      { id: 3, nombre: "Classic" }
    ],
    temporadas: [
      { id: 1, nombre: "Primavera 2026" },
      { id: 2, nombre: "Verano 2026" },
      { id: 3, nombre: "Otoño 2026" }
    ],
    setsTallas: [
      { id: 1, nombre: "Dama 35-38", tallas: [35, 36, 37, 38] },
      { id: 2, nombre: "Dama 34-39", tallas: [34, 35, 36, 37, 38, 39] },
      { id: 3, nombre: "Caballero 39-43", tallas: [39, 40, 41, 42, 43] },
      { id: 4, nombre: "Niña 27-33", tallas: [27, 28, 29, 30, 31, 32, 33] }
    ],
    estilos: {
      "ST-1001": {
        codigo: "ST-1001",
        descripcion: "Balerina clásica",
        marca: "Pretty",
        colores: [
          {
            id: "NAVY",
            nombre: "Navy",
            color_base: "Azul",
            material_default: "Sintético",
            precio: 147,
            imagen: "https://via.placeholder.com/80x60?text=ST1001",
            materiales: ["Sintético", "Cuero", "Textil"]
          },
          {
            id: "BEIGE",
            nombre: "Beige",
            color_base: "Crema",
            material_default: "Sintético",
            precio: 149,
            imagen: "https://via.placeholder.com/80x60?text=ST1001",
            materiales: ["Sintético", "Textil"]
          }
        ]
      },
      "ST-2005": {
        codigo: "ST-2005",
        descripcion: "Tenis urbano",
        marca: "Urban Step",
        colores: [
          {
            id: "BLACK",
            nombre: "Black",
            color_base: "Negro",
            material_default: "Textil",
            precio: 189,
            imagen: "https://via.placeholder.com/80x60?text=ST2005",
            materiales: ["Textil", "Sintético"]
          },
          {
            id: "WHITE",
            nombre: "White",
            color_base: "Blanco",
            material_default: "Sintético",
            precio: 185,
            imagen: "https://via.placeholder.com/80x60?text=ST2005",
            materiales: ["Sintético", "Cuero"]
          }
        ]
      },
      "ST-3010": {
        codigo: "ST-3010",
        descripcion: "Sandalia de dama",
        marca: "Pretty",
        colores: [
          {
            id: "ROSE",
            nombre: "Rose Gold",
            color_base: "Rosado",
            material_default: "Sintético",
            precio: 163,
            imagen: "https://via.placeholder.com/80x60?text=ST3010",
            materiales: ["Sintético", "Cuero"]
          }
        ]
      }
    }
  };

  // =========================
  // Estado
  // =========================
  var state = {
    detalle: [],
    editIndex: -1,
    estiloActual: null,
    colorActual: null
  };

  // =========================
  // DOM
  // =========================
  var $cliente = document.getElementById("cliente");
  var $marca = document.getElementById("marca");
  var $fechaDesde = document.getElementById("fechaDesde");
  var $fechaHasta = document.getElementById("fechaHasta");
  var $temporada = document.getElementById("temporada");
  var $setTallas = document.getElementById("setTallas");
  var $observacionesPedido = document.getElementById("observacionesPedido");

  var $codigoEstilo = document.getElementById("codigoEstilo");
  var $descripcionEstilo = document.getElementById("descripcionEstilo");
  var $color = document.getElementById("color");
  var $material = document.getElementById("material");
  var $precio = document.getElementById("precio");
  var $observacionesLinea = document.getElementById("observacionesLinea");
  var $areaTallas = document.getElementById("areaTallas");
  var $alertaProducto = document.getElementById("alertaProducto");

  var $detallePedidoBody = document.getElementById("detallePedidoBody");
  var $filaSinDatos = document.getElementById("filaSinDatos");

  var $resumenLineas = document.getElementById("resumenLineas");
  var $resumenPares = document.getElementById("resumenPares");
  var $resumenMonto = document.getElementById("resumenMonto");
  var $resumenSet = document.getElementById("resumenSet");

  var $jsonWrap = document.getElementById("jsonWrap");
  var $jsonSalida = document.getElementById("jsonSalida");

  // =========================
  // Helpers
  // =========================
  function formatMoney(value) {
    value = Number(value || 0);
    return "Q " + value.toLocaleString("es-GT", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function fillSelect(select, items, placeholder, valueField, textField) {
    select.innerHTML = "";
    var opt0 = document.createElement("option");
    opt0.value = "";
    opt0.textContent = placeholder || "Seleccione...";
    select.appendChild(opt0);

    items.forEach(function (item) {
      var opt = document.createElement("option");
      opt.value = item[valueField];
      opt.textContent = item[textField];
      select.appendChild(opt);
    });
  }

  function getSetSeleccionado() {
    var id = Number($setTallas.value || 0);
    return dataDemo.setsTallas.find(function (s) { return s.id === id; }) || null;
  }

  function getCantidadesTallas() {
    var inputs = $areaTallas.querySelectorAll("[data-talla]");
    var cantidades = {};
    var total = 0;

    inputs.forEach(function (input) {
      var talla = input.getAttribute("data-talla");
      var qty = Number(input.value || 0);
      cantidades[talla] = qty;
      total += qty;
    });

    return {
      cantidades: cantidades,
      total: total
    };
  }

  function getColorSeleccionado() {
    if (!state.estiloActual) return null;
    return state.estiloActual.colores.find(function (c) {
      return c.id === $color.value;
    }) || null;
  }

  function renderTallas() {
    var set = getSetSeleccionado();
    $areaTallas.innerHTML = "";

    if (!set) {
      $alertaProducto.className = "alert alert-info py-2 mb-3";
      $alertaProducto.innerHTML = 'Seleccione un <strong>set de tallas</strong> para habilitar el ingreso.';
      return;
    }

    $resumenSet.textContent = set.nombre;

    var wrapper = document.createElement("div");
    wrapper.className = "size-grid";

    set.tallas.forEach(function (talla) {
      var box = document.createElement("div");
      box.className = "size-box";

      box.innerHTML =
        '<label>Talla ' + talla + '</label>' +
        '<input type="number" min="0" step="1" class="form-control form-control-sm" data-talla="' + talla + '" value="0">';

      wrapper.appendChild(box);
    });

    $areaTallas.appendChild(wrapper);

    if (!state.estiloActual) {
      $alertaProducto.className = "alert alert-warning py-2 mb-3";
      $alertaProducto.innerHTML = 'Ahora cargue un <strong>estilo</strong> para completar color, material y precio.';
    } else {
      $alertaProducto.className = "alert alert-success py-2 mb-3";
      $alertaProducto.innerHTML = 'Ingrese la cantidad por talla y agregue la línea.';
    }
  }

  function cargarEstilo() {
    var codigo = ($codigoEstilo.value || "").trim().toUpperCase();
    var estilo = dataDemo.estilos[codigo];

    state.estiloActual = null;
    state.colorActual = null;
    $descripcionEstilo.value = "";
    $color.innerHTML = '<option value="">Seleccione...</option>';
    $material.innerHTML = '<option value="">Seleccione...</option>';
    $precio.value = "";

    if (!codigo) {
      alert("Ingrese un código de estilo.");
      return;
    }

    if (!estilo) {
      alert("No se encontró el estilo ingresado.");
      return;
    }

    state.estiloActual = estilo;
    $descripcionEstilo.value = estilo.descripcion;

    var colorOptions = '<option value="">Seleccione...</option>';
    estilo.colores.forEach(function (c) {
      colorOptions += '<option value="' + c.id + '">' + c.nombre + ' / ' + c.color_base + '</option>';
    });
    $color.innerHTML = colorOptions;

    if ($marca.value === "") {
      var marcaExiste = dataDemo.marcas.find(function (m) { return m.nombre === estilo.marca; });
      if (marcaExiste) {
        $marca.value = String(marcaExiste.id);
      }
    }

    if (estilo.colores.length === 1) {
      $color.value = estilo.colores[0].id;
      onColorChange();
    }

    if (getSetSeleccionado()) {
      $alertaProducto.className = "alert alert-success py-2 mb-3";
      $alertaProducto.innerHTML = 'Estilo cargado. Complete color, material, precio y cantidades.';
    } else {
      $alertaProducto.className = "alert alert-warning py-2 mb-3";
      $alertaProducto.innerHTML = 'Estilo cargado. Falta seleccionar un <strong>set de tallas</strong>.';
    }
  }

  function onColorChange() {
    var color = getColorSeleccionado();
    state.colorActual = color;
    $material.innerHTML = '<option value="">Seleccione...</option>';
    $precio.value = "";

    if (!color) return;

    color.materiales.forEach(function (mat) {
      var opt = document.createElement("option");
      opt.value = mat;
      opt.textContent = mat;
      $material.appendChild(opt);
    });

    $material.value = color.material_default;
    $precio.value = color.precio;
  }

  function limpiarLinea() {
    state.editIndex = -1;
    state.estiloActual = null;
    state.colorActual = null;

    $codigoEstilo.value = "";
    $descripcionEstilo.value = "";
    $color.innerHTML = '<option value="">Seleccione...</option>';
    $material.innerHTML = '<option value="">Seleccione...</option>';
    $precio.value = "";
    $observacionesLinea.value = "";

    renderTallas();

    var inputs = $areaTallas.querySelectorAll("[data-talla]");
    inputs.forEach(function (input) {
      input.value = 0;
    });

    document.getElementById("btnAgregarLinea").textContent = "Agregar producto";
  }

  function validarEncabezado() {
    if (!$cliente.value) return "Seleccione el cliente.";
    if (!$marca.value) return "Seleccione la marca.";
    if (!$fechaDesde.value) return "Ingrese la fecha de entrega desde.";
    if (!$fechaHasta.value) return "Ingrese la fecha de entrega hasta.";
    if (!$temporada.value) return "Seleccione la temporada.";
    if (!$setTallas.value) return "Seleccione el set de tallas.";
    if ($fechaHasta.value < $fechaDesde.value) return "La fecha hasta no puede ser menor que la fecha desde.";
    return "";
  }

  function agregarLinea() {
    var errorHeader = validarEncabezado();
    if (errorHeader) {
      alert(errorHeader);
      return;
    }

    if (!state.estiloActual) {
      alert("Debe cargar un estilo.");
      return;
    }

    var color = getColorSeleccionado();
    if (!color) {
      alert("Seleccione un color.");
      return;
    }

    if (!$material.value) {
      alert("Seleccione el material.");
      return;
    }

    var precio = Number($precio.value || 0);
    if (precio <= 0) {
      alert("El precio debe ser mayor a cero.");
      return;
    }

    var tallaInfo = getCantidadesTallas();
    if (tallaInfo.total <= 0) {
      alert("Debe ingresar al menos una cantidad en una talla.");
      return;
    }

    var marcaObj = dataDemo.marcas.find(function (m) { return String(m.id) === String($marca.value); });
    var set = getSetSeleccionado();

    var linea = {
      estilo_codigo: state.estiloActual.codigo,
      estilo_descripcion: state.estiloActual.descripcion,
      color_id: color.id,
      color_nombre: color.nombre,
      color_base: color.color_base,
      marca: marcaObj ? marcaObj.nombre : "",
      material: $material.value,
      precio: precio,
      imagen: color.imagen,
      tallas: set.tallas.slice(),
      cantidades: tallaInfo.cantidades,
      cantidad_total: tallaInfo.total,
      subtotal: tallaInfo.total * precio,
      observaciones: $observacionesLinea.value || ""
    };

    if (state.editIndex >= 0) {
      state.detalle[state.editIndex] = linea;
    } else {
      state.detalle.push(linea);
    }

    renderDetalle();
    renderResumen();
    renderJson();
    limpiarLinea();
  }

  function editarLinea(index) {
    var linea = state.detalle[index];
    state.editIndex = index;

    $codigoEstilo.value = linea.estilo_codigo;
    cargarEstilo();
    $color.value = linea.color_id;
    onColorChange();
    $material.value = linea.material;
    $precio.value = linea.precio;
    $observacionesLinea.value = linea.observaciones;

    var inputs = $areaTallas.querySelectorAll("[data-talla]");
    inputs.forEach(function (input) {
      var talla = input.getAttribute("data-talla");
      input.value = Number(linea.cantidades[talla] || 0);
    });

    document.getElementById("btnAgregarLinea").textContent = "Actualizar producto";
    window.scrollTo({ top: document.getElementById("pedidoApp").offsetTop, behavior: "smooth" });
  }

  function eliminarLinea(index) {
    if (!confirm("¿Desea eliminar esta línea?")) return;
    state.detalle.splice(index, 1);
    renderDetalle();
    renderResumen();
    renderJson();
  }

  function renderDetalle() {
    $detallePedidoBody.innerHTML = "";

    if (!state.detalle.length) {
      $detallePedidoBody.innerHTML = '<tr id="filaSinDatos"><td colspan="12" class="text-center text-muted py-4">No hay productos agregados.</td></tr>';
      return;
    }

    state.detalle.forEach(function (linea, index) {
      var tallasStr = linea.tallas.join(" ");
      var cantidadesStr = linea.tallas.map(function (t) {
        return linea.cantidades[t] || 0;
      }).join(" ");

      var tr = document.createElement("tr");
      tr.innerHTML =
        '<td><strong>' + linea.estilo_codigo + '</strong><br><small class="text-muted">' + linea.estilo_descripcion + '</small></td>' +
        '<td>' + linea.color_nombre + '</td>' +
        '<td>' + linea.color_base + '</td>' +
        '<td>' + linea.marca + '</td>' +
        '<td>' + linea.material + '</td>' +
        '<td class="text-right">' + formatMoney(linea.precio) + '</td>' +
        '<td><img src="' + linea.imagen + '" class="shoe-thumb" alt=""></td>' +
        '<td class="sizes-compact"><div class="sizes-labels">' + tallasStr + '</div><div class="sizes-values text-muted">' + cantidadesStr + '</div></td>' +
        '<td class="text-center"><strong>' + linea.cantidad_total + '</strong></td>' +
        '<td class="text-right"><strong>' + formatMoney(linea.subtotal) + '</strong></td>' +
        '<td>' + (linea.observaciones || "") + '</td>' +
        '<td>' +
          '<button type="button" class="btn btn-warning btn-sm mr-1 btn-editar" data-index="' + index + '">Editar</button>' +
          '<button type="button" class="btn btn-danger btn-sm btn-eliminar" data-index="' + index + '">Eliminar</button>' +
        '</td>';

      $detallePedidoBody.appendChild(tr);
    });

    bindDetalleActions();
  }

  function bindDetalleActions() {
    var botonesEditar = document.querySelectorAll(".btn-editar");
    var botonesEliminar = document.querySelectorAll(".btn-eliminar");

    botonesEditar.forEach(function (btn) {
      btn.addEventListener("click", function () {
        editarLinea(Number(btn.getAttribute("data-index")));
      });
    });

    botonesEliminar.forEach(function (btn) {
      btn.addEventListener("click", function () {
        eliminarLinea(Number(btn.getAttribute("data-index")));
      });
    });
  }

  function renderResumen() {
    var totalLineas = state.detalle.length;
    var totalPares = 0;
    var totalMonto = 0;

    state.detalle.forEach(function (linea) {
      totalPares += Number(linea.cantidad_total || 0);
      totalMonto += Number(linea.subtotal || 0);
    });

    $resumenLineas.textContent = totalLineas;
    $resumenPares.textContent = totalPares;
    $resumenMonto.textContent = formatMoney(totalMonto);
  }

  function buildPayload() {
    return {
      cliente_id: $cliente.value,
      marca_id: $marca.value,
      fecha_entrega_desde: $fechaDesde.value,
      fecha_entrega_hasta: $fechaHasta.value,
      temporada_id: $temporada.value,
      set_tallas_id: $setTallas.value,
      observaciones: $observacionesPedido.value || "",
      detalle: state.detalle.map(function (linea) {
        return {
          estilo_codigo: linea.estilo_codigo,
          color_id: linea.color_id,
          material: linea.material,
          precio: linea.precio,
          tallas: linea.tallas.map(function (talla) {
            return {
              talla: talla,
              cantidad: Number(linea.cantidades[talla] || 0)
            };
          }),
          observaciones: linea.observaciones || ""
        };
      })
    };
  }

  function renderJson() {
    $jsonWrap.classList.remove("d-none");
    $jsonSalida.value = JSON.stringify(buildPayload(), null, 2);
  }

  function limpiarPedido() {
    if (state.detalle.length && !confirm("Se perderá la información cargada. ¿Desea continuar?")) {
      return;
    }

    state.detalle = [];
    state.editIndex = -1;

    $cliente.value = "";
    $marca.value = "";
    $fechaDesde.value = "";
    $fechaHasta.value = "";
    $temporada.value = "";
    $observacionesPedido.value = "";
    $setTallas.value = "";

    limpiarLinea();
    renderDetalle();
    renderResumen();
    $jsonWrap.classList.add("d-none");
    $jsonSalida.value = "";
    $resumenSet.textContent = "Sin seleccionar";
  }

  function guardarPedido() {
    var errorHeader = validarEncabezado();
    if (errorHeader) {
      alert(errorHeader);
      return;
    }

    if (!state.detalle.length) {
      alert("Debe agregar al menos una línea al pedido.");
      return;
    }

    var payload = buildPayload();
    renderJson();
    alert("Pedido validado. En producción aquí enviarías el payload a tu API.");
    console.log(payload);
  }

  function init() {
    fillSelect($cliente, dataDemo.clientes, "Seleccione...", "id", "nombre");
    fillSelect($marca, dataDemo.marcas, "Seleccione...", "id", "nombre");
    fillSelect($temporada, dataDemo.temporadas, "Seleccione...", "id", "nombre");
    fillSelect($setTallas, dataDemo.setsTallas, "Seleccione...", "id", "nombre");

    renderTallas();
    renderDetalle();
    renderResumen();

    document.getElementById("btnBuscarEstilo").addEventListener("click", cargarEstilo);
    $codigoEstilo.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        cargarEstilo();
      }
    });

    $color.addEventListener("change", onColorChange);
    $setTallas.addEventListener("change", renderTallas);

    document.getElementById("btnAgregarLinea").addEventListener("click", agregarLinea);
    document.getElementById("btnLimpiarLinea").addEventListener("click", limpiarLinea);
    document.getElementById("btnLimpiarPedido").addEventListener("click", limpiarPedido);
    document.getElementById("btnGuardarPedido").addEventListener("click", guardarPedido);
  }

  init();

  // Demo visual inicial
  $setTallas.value = "1";
  renderTallas();
  $cliente.value = "1";
  $marca.value = "1";
  $temporada.value = "1";
  $fechaDesde.value = "2026-03-20";
  $fechaHasta.value = "2026-03-30";
})();
</script>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <div class="right-sidebar">
                    <div class="slimscrollright">
                        <div class="rpanel-title"> Service Panel <span><i class="ti-close right-side-toggle"></i></span> </div>
                        <div class="r-panel-body">
                            <ul id="themecolors" class="m-t-20">
                                <li><b>With Light sidebar</b></li>
                                <li><a href="javascript:void(0)" data-theme="default" class="default-theme">1</a></li>
                                <li><a href="javascript:void(0)" data-theme="green" class="green-theme">2</a></li>
                                <li><a href="javascript:void(0)" data-theme="red" class="red-theme">3</a></li>
                                <li><a href="javascript:void(0)" data-theme="blue" class="blue-theme">4</a></li>
                                <li><a href="javascript:void(0)" data-theme="purple" class="purple-theme">5</a></li>
                                <li><a href="javascript:void(0)" data-theme="megna" class="megna-theme">6</a></li>
                                <li class="d-block m-t-30"><b>With Dark sidebar</b></li>
                                <li><a href="javascript:void(0)" data-theme="default-dark" class="default-dark-theme working">7</a></li>
                                <li><a href="javascript:void(0)" data-theme="green-dark" class="green-dark-theme">8</a></li>
                                <li><a href="javascript:void(0)" data-theme="red-dark" class="red-dark-theme">9</a></li>
                                <li><a href="javascript:void(0)" data-theme="blue-dark" class="blue-dark-theme">10</a></li>
                                <li><a href="javascript:void(0)" data-theme="purple-dark" class="purple-dark-theme">11</a></li>
                                <li><a href="javascript:void(0)" data-theme="megna-dark" class="megna-dark-theme ">12</a></li>
                            </ul>
                            <ul class="m-t-20 chatonline">
                                <li><b>Chat option</b></li>
                                <li>

                                </li>
                                <li>

                                </li>
                                <li>

                                </li>
                                <li>

                                </li>
                                <li>

                                </li>
                                <li>

                                </li>
                                <li>

                                </li>
                                <li>

                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->




            <footer class="footer">
                <?php echo $footer_sistema ?>
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="js/perfect-scrollbar.jquery.min.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="../assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <script src="../assets/plugins/sparkline/jquery.sparkline.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.min.js"></script>
    <script src="js/jasny-bootstrap.js"></script>
    <script src="../assets/plugins/toast-master/js/jquery.toast.js"></script>
    <script src="../assets/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="../assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    <!-- This is data table -->
    <script src="../assets/plugins/datatables/datatables.min.js"></script>
    <!-- start - This is for export functionality only -->
    <script src="../assets/plugins/datatables/media/js/dataTables.buttons.min.js"></script>
    <script src="../assets/plugins/datatables/media/js/buttons.flash.min.js"></script>
    <script src="../assets/plugins/jszip.min.js"></script>
    <script src="../assets/plugins/datatables/media/js/buttons.html5.min.js"></script>
    <script src="../assets/plugins/datatables/media/js/buttons.print.min.js"></script>

    <!-- jQuery peity -->
    <script src="../assets/plugins/peity/jquery.peity.min.js"></script>
    <script src="../assets/plugins/peity/jquery.peity.init.js"></script>
    <!-- Selects -->
    <script src="../assets/plugins/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <!-- end - This is for export functionality only -->
    <!-- Modal -->
  <!-- Modal -->
    <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="miModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="miModalLabel">Ingrese PIN</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p id="actividad"></p>
            <input type="password" class="form-control" id="pin" name="pin" placeholder="PIN">
            <input type="hidden" name="idactividad_realizada" id="idactividad_realizada">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="
                callback_guardar_usuario_actividad = function(respuesta) {
                    if ( respuesta == 'abierta' ) {
                        notify_success('INICIO DE ACTIVIDAD ');
                        $('#miModal').modal('hide');
                    } else if ( respuesta == 'cerrada' ) {
                        notify_success('FIN DE ACTIVIDAD ');
                        $('#miModal').modal('hide');
                    } else {
                        notify_error(respuesta);
                    }
                }
                upload_action('pin,idactividad_realizada', 'actividad', 'guardar_usuario_actividad', callback_guardar_usuario_actividad  )
            "
            >Grabar</button>
        </div>
        </div>
    </div>
    </div>


</body>

</html>