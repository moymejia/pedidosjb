<?php
if (!isset($_SESSION)) {
    session_start();
} else {
    session_destroy();
    session_unset();
}

?>
<!DOCTYPE html>
<html lang="en">

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- page css -->
    <link href="css/pages/login-register-lock.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">

    <!-- You can change the theme colors from here -->
    <link href="css/colors/default-dark.css" id="theme" rel="stylesheet">
    <link href="../css/common.css" rel="stylesheet">
    <script src="../js/login.js?x=<?php echo date('YmdHis'); ?>"></script>
    <script src="../js/common.js?x=<?php echo date('YmdHis'); ?>"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body class="card-no-border" >

    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="loader">
            <div class="loader__figure"></div>
            <p class="loader__label">Wisetech Solutions</p>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <section id="wrapper">

        <div class="login-bg">

            <div class="login-card">

                <!-- HEADER CON IMAGEN -->
                <div class="login-header">
                    <div class="header-overlay">

                        <h1>Bienvenido a PedidosJB</h1>
                        <h3>Sistema de gestión de pedidos</h3>

                    </div>
                </div>


                <!-- FORMULARIO -->
                <div class="login-body">

                    <form id="loginform" onsubmit="return login(); return false;">

                        <input type="hidden" id="token" name="token" value="uno">

                        <div class="input-group-custom">
                            <i class="bi bi-person input-icon"></i>
                            <input class="login-input" type="text" id="usuario" name="usuario" placeholder="Usuario" required>
                        </div>
                        <br>
                        <div class="input-group-custom">
                            <i class="bi bi-lock input-icon"></i>
                            <input class="login-input" type="password" id="clave" name="clave" placeholder="Clave" required>
                        </div>
                        <br>
                        <button class="login-btn" type="submit">
                            Ingresar
                        </button>

                    </form>

                </div>

                <p class="login-watermark">By WiseTech Solutions</p>

            </div>

        </div>

    </section>

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
    <script src="../assets/plugins/toast-master/js/jquery.toast.js"></script>
    <!--Custom JavaScript -->
    <script type="text/javascript">
        $(function() {
            $(".preloader").fadeOut();
        });
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        });
        // ==============================================================
        // Login and Recover Password
        // ==============================================================
        $('#to-recover').on("click", function() {
            $("#loginform").slideUp();
            $("#recoverform").fadeIn();
        });

    </script>

</body>

</html>