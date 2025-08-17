<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" href="../resources/images/logo/logo.png">
        <title> Entre vecinos | Si lo tengo, vecina</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.6 -->
        <!--        <link rel="stylesheet" href="../util/bootstrap/css/bootstrap.min.css">
                 Font Awesome 
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
                 Ionicons 
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
                 Theme style 
                <link rel="stylesheet" href="../util/lte/css/AdminLTE.min.css">
                 iCheck 
                <link rel="stylesheet" href="../util/plugins/iCheck/square/blue.css">-->
        <?php include_once 'estilos.view.php'; ?>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition login-page bg-gray-light">
        <!--<body class="hold-transition login-page alert-warning">-->
        <div class="login-box">
            <!--<br/><br/><br/><br/>-->  
            <div class="login-box-body">  
                <div class="logo">
                    <img src="../resources/images/logo/logo.png" class="col-lg-6 img-responsive">   
                </div>    
                <div class="login-logo">
                    <a href="#"><b>Iniciar</b> Sesión</a>
                </div>
                <!-- /.login-logo -->

                <p class="login-box-msg">Ingrese sus datos para iniciar sesión</p>

                <form action="../controllers/loginController.php" method="post">
                    <!--                    <div class="form-inline text-right has-feedback">
                                            
                                            <label>
                                                 <select size="1" class="form-control input-sm" id="txtTipo" name="txtTipo">     
                                                    <option>Tipo Usuario</option>
                                                    <option value="A">Administrador</option>
                                                    <option value="P">Postulante</option>
                                                </select>
                                            </label>
                                        </div>-->
                    <div class="form-group has-feedback">
                        <input type="email" class="form-control" placeholder="Email" name="txtEmail" required="" autofocus="">
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" placeholder="Password" name="txtClave" required="">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="row">
                        <!--                        <div class="col-lg-12">
                                                    <div class="checkbox icheck">
                                                        <label>
                                                            <input type="checkbox"> Recordar mis datos
                                                        </label>
                                                    </div>
                                                </div>-->
                        <!-- /.col -->
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-warning btn-block btn-flat"> <i class="glyphicon glyphicon-log-in"></i> Ingresar</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

                <!--<div class="social-auth-links text-center">
                  <p>- OR -</p>
                  <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in using
                    Facebook</a>
                  <a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i> Sign in using
                    Google+</a>
                </div> -->
                <!-- /.social-auth-links -->
                <br/><br/>
                <div class="text-center">
                    <p>¿No tiene una cuenta?</p>
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#myModal" id="btnagregar"><i class="fa fa-user"> Crear una </i></button>
                    <!--<a href="registrate.usuario.view.php" class=""> Cree una.</a>-->
                    <!--<a href="register.html" class="text-center">Register a new membership</a>-->
                </div>
            </div><br/>
            <!-- /.login-box-body -->
            <div class="text-center">
                <p class="text-black">
                    ©<?php echo date('Y'); ?> 
                    Copyright - VICSAC. Todos los derechos reservados.
                </p>
            </div>
        </div>
        <section class="content">

            <!-- INICIO del formulario modal -->
            <small>
                <form id="frmgrabar">
                    <div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" id=""><b>Crear nuevo usuario</b></h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <p>
                                                <input type="hidden" value="" id="txtTipoOperacion" name="txtTipoOperacion">
                                                Código <input type="text" 
                                                              name="txtCodigo" 
                                                              id="txtCodigo" 
                                                              class="form-control input-sm text-bold" 
                                                              readonly="">
                                            </p>
                                        </div>
                                        <div class="col-xs-3">
                                            <p>
                                                Dni (*) <input type="text" class="form-control" 
                                                               id="txtDni" name="txtDni" 
                                                               required="" autofocus="" 
                                                               maxlength="8"
                                                               onkeypress="ValidaSoloNumeros();">
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <p>
                                                Nombres (*)
                                                <input type="text" class="form-control" id="txtNombre" name="txtNombre" required="" autofocus="">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Apellidos (*)
                                                <input type="text" class="form-control" id="txtApellidos" name="txtApellidos" required="" autofocus="">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Dirección (*)
                                                <input type="text" class="form-control" id="txtDireccion" name="txtDireccion" required="" autofocus="">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Estado Civil (*)
                                                <select size="1" id="estado_civil" name="estado_civil" class="form-control has-feedback-left" required> 
                                                    <option></option>
                                                    <option value="Soltero">Soltero</option>
                                                    <option value="Casado">Casado</option>
                                                    <option value="Viudo">Viudo</option>
                                                    <option value="Divorciado">Divorciado</option>
                                                </select>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <p>
                                                Departamento (*)
                                                <input type="text" class="form-control" id="txtDepartamento" name="txtDepartamento" required="" autofocus="">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Provincia (*)
                                                <input type="text" id="txtProvincia" class="form-control" name="txtProvincia" required="" autofocus="">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Email (*)
                                                <input type="email" id="txtEmail" class="form-control" name="txtEmail" required="" onChange="javascript:document.getElementById('cuenta').value = this.value;">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Teléfono (*)
                                                <input type="text" id="txtTelefono" class="form-control" name="txtTelefono" required="" 
                                                       autofocus="" maxlength="8" onkeypress="ValidaSoloNumeros();">
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Sexo (*)
                                                <select size="1" id="sexo" name="sexo" class="form-control has-feedback-left" required> 
                                                    <option></option>
                                                    <option value="H">Hombre</option>
                                                    <option value="M">Mujer</option>
                                                </select>
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Edad (*)
                                                <select size="1" id="edad" name="edad" class="form-control has-feedback-left" required> 
                                                    <option></option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                    <option value="32">32</option>
                                                    <option value="33">33</option>
                                                    <option value="34">34</option>
                                                    <option value="35">35</option>
                                                    <option value="36">36</option>
                                                    <option value="37">37</option>
                                                    <option value="38">38</option>
                                                    <option value="39">39</option>
                                                    <option value="40">40</option>
                                                    <option value="41">41</option>
                                                    <option value="42">42</option>
                                                    <option value="43">43</option>
                                                    <option value="44">44</option>
                                                    <option value="45">45</option>
                                                    <option value="46">46</option>
                                                    <option value="47">47</option>
                                                    <option value="48">48</option>
                                                    <option value="49">49</option>
                                                    <option value="50">50</option>
                                                    <option value="51">51</option>
                                                    <option value="52">52</option>
                                                    <option value="53">53</option>
                                                    <option value="54">54</option>
                                                    <option value="55">55</option>
                                                    <option value="56">56</option>
                                                    <option value="57">57</option>
                                                    <option value="58">58</option>
                                                    <option value="59">59</option>
                                                    <option value="60">60</option>
                                                </select>
                                            </p>
                                        </div>
                                        <div class="col-xs-6">
                                            <p>
                                                Hijos (*)
                                                <select size="1" id="hijo" name="hijo" class="form-control has-feedback-left" required> 
                                                    <option></option>
                                                    <option value="NO">No</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="a más">a más</option>
                                                </select>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id=""><b>Usuario</b></h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <p>
                                                        <b>Usuario (*)</b>
                                                        <input type="text" name="cuenta" class="form-control has-feedback-left" id="cuenta" readonly="true">
                                                    </p>
                                                </div>
                                                <div class="col-xs-6">
                                                    <p>
                                                        <b>Contraseña (*)</b>
                                                        <input type="password" name="contrasenia" class="form-control has-feedback-left" id="contrasenia" required>
                                                    </p>
                                                </div>
                                                <div class="col-xs-6">
                                                    <p>
                                                        (*) Campo Obligatorio
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-warning" aria-hidden="true"><i class="fa fa-save"></i> Grabar</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="btncerrar"><i class="fa fa-close"></i> Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </small>
            <!-- FIN del formulario modal -->

        </section>
        <?php include_once './scripts.view.php'; ?>
        <!-- /.login-box -->

        <!-- jQuery 2.2.3 -->
<!--        <script src="../util/plugins/jQuery/jquery-2.2.3.min.js"></script>
         Bootstrap 3.3.6 
        <script src="../util/bootstrap/js/bootstrap.min.js"></script>
         iCheck 
        <script src="../util/plugins/iCheck/icheck.min.js"></script>-->
<!--        <script>
            $(function () {
                $('input').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%' // optional
                });
            });
        </script>-->
        <script src="js/registrate.usuario.js" type="text/javascript"></script>
    </body>
</html>
