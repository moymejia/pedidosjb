<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    die();
}
require_once '../php/wisetech/mysql.php';
$mysql              = new mysql(prefijo . 'seguridad');
$encabezado_sistema = $mysql->getvalue("SELECT valor FROM configuracion WHERE clave = 'encabezado_sistema' ");
$usuario            = $_SESSION['usuario'];
$nombre             = $_SESSION['usuario_nombre'];
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
    <link href="../css/common.css" rel="stylesheet">
    <link href="../assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css" />
    <!-- You can change the theme colors from here -->
    <link href="css/pages/floating-label.css" rel="stylesheet">
    <link href="css/pages/tab-page.css" rel="stylesheet">
    <link href="css/colors/default-dark.css" id="theme" rel="stylesheet">

    <script src="../js/main.js?x=<?php echo date('YmdHis'); ?>"></script>
    <script src="../js/common.js?x=<?php echo date('YmdHis'); ?>""></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="fix-header card-no-border fix-sidebar" onload=" " >

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
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header" >
                    <div style="margin: auto;width: 50%;padding: 10px;color: black;font-size: 1.5vw;white-space:nowrap;overflow:visible; ">
                        <?php echo $ubicacion; ?>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav mr-auto">
                        <!-- This is  -->
                        <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                        <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                        <li class="nav-item hidden-sm-down"></li>
                        <span id='encabezado_sistema'><?php echo $encabezado_sistema ?></span>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav my-lg-0">
                        <li><a href="../" class="m-r-10"><i class="ti-user"></i> Salir</a></li>
                    </ul>
                    <ul class="navbar-nav my-lg-0" style="display: none;">
                        <!-- ============================================================== -->
                        <!-- Language -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="flag-icon flag-icon-us"></i></a>
                            <div class="dropdown-menu dropdown-menu-right animated bounceInDown"> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-in"></i> India</a> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-fr"></i> French</a> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-cn"></i> China</a> <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-de"></i> Dutch</a> </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- Profile -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
                            <div class="dropdown-menu dropdown-menu-right animated flipInY">
                                <ul class="dropdown-user">
                                    <li>
                                        <div class="dw-user-box">
                                            <div class="u-img"><img src="../assets/images/users/1.jpg" alt="user"></div>
                                            <div class="u-text">
                                                <h4>Steave Jobs</h4>
                                                <p class="text-muted">varun@gmail.com</p><a href="pages-profile.html" class="btn btn-rounded btn-danger btn-sm">View Profile</a></div>
                                        </div>
                                    </li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="#"><i class="ti-user"></i> My Profile</a></li>
                                    <li><a href="#"><i class="ti-wallet"></i> My Balance</a></li>
                                    <li><a href="#"><i class="ti-email"></i> Inbox</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="#"><i class="ti-settings"></i> Account Setting</a></li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="#"><i class="fa fa-power-off"></i> Logout</a></li>
                                </ul>
                            </div>
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
        <aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li class="user-profile"> <a class="has-arrow waves-effect waves-dark" href="" aria-expanded="false"><i class="mdi mdi-account-circle"></i><span class="hide-menu"> <?php echo $nombre ?> </span></a>

                        </li>
                        <li class="nav-devider"></li>
                        <li class="nav-small-cap">OPCIONES</li>
                        <input type='hidden' id='idopcion_actual'>
                        <?php
$idrol  = $mysql->getvalue("SELECT idrol FROM usuario WHERE usuario = '$usuario' ", 'idrol');
$result = $mysql->getresult("SELECT idmenu, menu,  icono FROM view_permisos WHERE idrol = 1 GROUP BY idmenu ORDER BY orden_menu ");
while ($row = $mysql->getrowresult($result)) {
    $menu              = $row['menu'];
    $icono_menu        = $row['icono'];
    $idmenu            = $row['idmenu'];
    $cantidad_opciones = $mysql->getvalue("SELECT COUNT(DISTINCT idopcion) opciones FROM view_permisos WHERE idrol = $idrol AND idmenu = $idmenu  ", "opciones");
    if ($cantidad_opciones > 0) {
        echo "<li> <a class=\"has-arrow waves-effect waves-dark\" href=\"#\" aria-expanded=\"false\"><i class=\"$icono_menu\"></i><span class=\"hide-menu\">$menu</span></a>
                                                            <ul aria-expanded=\"false\" class=\"collapse\">";
        $result_opciones = $mysql->getresult("SELECT idopcion, opcion FROM view_permisos WHERE idrol = '$idrol' and idmenu = '$idmenu' GROUP BY idopcion  ORDER BY  orden_opcion ");
        while ($row_opcion = $mysql->getrowresult($result_opciones)) {
            $opcion   = $row_opcion['opcion'];
            $idopcion = $row_opcion['idopcion'];
            echo "<li><a href=\"#\" class='' onclick=\"mostrar_opcion($idopcion, '$opcion','$menu');\">$opcion</a></li>";
        }
        echo "</ul>
                                                            </li>";
    }
}
?>
                            <!-- -->

                            <!-- -->
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
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
                        <h3 class="text-themecolor" id="titulo_actual"></h3>
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
                            <div class="card-body" id="contenedor_principal" style="  min-height: 600px;"   >
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
                By WiseTech.Solutions
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