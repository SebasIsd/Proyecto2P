<?php
// Incluir la consulta de autoridades
require './home/autoridades.php';
?>


<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <link rel="shortcut icon" href="images/favico.png" type="">

  <title> Eventos - UTA </title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <!-- nice select  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css"
    integrity="sha512-CruCP+TD3yXzlvvijET8wV5WxxEh5H8P4cmz0RFbKK6FlZ2sYl3AEsKlLPHbniXKSrDdFewhbmBK5skbdsASbQ=="
    crossorigin="anonymous" />
  <!-- font awesome style -->
  <link href="css/font-awesome.min.css" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estiloslogin.css">

</head>

<body>

  <div class="hero_area">
    <div class="bg-box">
      <img src="images/hero-bg.jpg" alt="">
    </div>
    <!-- header section strats -->
    <header class="header_section">
      <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
          <a class="navbar-brand" href="index.html">
            <span>
              Eventos disponibles
            </span>
          </a>

          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class=""> </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav  mx-auto ">
              <li class="nav-item active">
                <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="">Eventos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="contacto.php">Contactanos</a>
              </li>
            </ul>
            <div class="user_option">
              <a href="" class="user_link">
              </a>
              <a class="cart_link" href="#">
                
                  <g>
                    <g>
                     
                    </g>
                  </g>
                  <g>
                    <g>
                      
                    </g>
                  </g>
                  <g>
                    <g>
                    
                    </g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                  <g>
                  </g>
                </svg>
              </a>
              <form class="form-inline">
                <button class="btn  my-2 my-sm-0 nav_search-btn" type="submit">
                </button>
              </form>
              <a href="#" class="order_online" data-bs-toggle="modal" data-bs-target="#loginModal">login</a>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- end header section -->
    <!-- slider section -->
     <?php
$sliderData = include __DIR__ . "/home/slider.php";
// Validación segura
if (!is_array($sliderData)) {
    $sliderData = [
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"],
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"],
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"]
    ];
}
?>

    <section class="slider_section ">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                     <?= $sliderData[0]["titulo"] ?>
                    </h1>
                    <p>
                    <?= $sliderData[0]["descripcion"] ?> 
                    </p>
                    <div class="btn-box">
                      <a href="" class="btn1">
                        Ver mas Informacion
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item ">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      <?= $sliderData[1]["titulo"] ?>
                    </h1>
                    <p>
                     <?= $sliderData[1]["descripcion"] ?> 
                    </p>
                    <div class="btn-box">
                      <a href="" class="btn1">
                        Contactanos
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                       <?= $sliderData[2]["titulo"] ?>
                    </h1>
                    <p>
                    <?= $sliderData[2]["descripcion"] ?> 
                    </p>
                    <div class="btn-box">
                      <a href="" class="btn1">
                        Saber mas
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="container">
          <ol class="carousel-indicators">
            <li data-target="#customCarousel1" data-slide-to="0" class="active"></li>
            <li data-target="#customCarousel1" data-slide-to="1"></li>
            <li data-target="#customCarousel1" data-slide-to="2"></li>
          </ol>
        </div>
      </div>

    </section>
    <!-- end slider section -->
  </div>
  <br>
  <!-- about section -->
   <?php
$nosData = include __DIR__ . "/home/contactanos.php";
// Validación segura
if (!is_array($nosData)) {
    $nosData = [
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"],
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"],
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"]
    ];
}
?>

  <section class="about_section layout_padding">
    <div class="container  ">

      <div class="row">
        <div class="col-md-6 ">
          <div class="img-box">
            <img src="images/nosotros/<?= htmlspecialchars($nosData["ruta"]) ?>" alt="Nosotros">

          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-box">
            <div class="heading_container">
              
              <h2>
                Nosotros
              </h2>
            </div>
            <p>
           <?= $nosData["des_noso"] ?>
            </p>
            <a href=<?= htmlspecialchars($nosData["link_noso"]) ?>>
              Read More
            </a>    
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end about section -->

 <!-- book section -->
<section class="book_section layout_padding">
  <div class="container">
    <div class="heading_container">
      <h2>Comentanos</h2>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form_container">
          <form id="formComentario">
            
            <!-- Nombre -->
            <div>
              <input type="text" class="form-control" name="nombre" id="campoNombre" placeholder="Tu Nombre" required />
            </div>

            <!-- Teléfono -->
            <div>
              <input type="text" class="form-control" name="telefono" id="telefono"
                     placeholder="Teléfono (09XXXXXXXX)" maxlength="10" required />
            </div>

            <!-- Correo -->
            <div>
              <input type="email" class="form-control" name="correo" id="correo"
                     placeholder="correo@uta.edu.ec" required />
            </div>

            <!-- Comentario -->
            <div>
              <textarea class="form-control" name="comentario" rows="4"
                        placeholder="Escribe tu comentario..." required></textarea>
            </div>

            <!-- Fecha actual bloqueada -->
            <div>
              <input type="date" class="form-control" name="fecha" id="fechaActual" readonly />
            </div>

            <div class="btn_box">
              <button type="submit">Enviar comentario</button>
            </div>

          </form>
        </div>
      </div>

      <div class="col-md-6">
        <div class="map_container">
          <div id="googleMap"></div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {

    // Detectar si viene desde el botón "Añadir comentario"
    const urlParams = new URLSearchParams(window.location.search);
    const from = urlParams.get('from');

    // Si viene desde ?from=add => enfocar automáticamente
    if (from === "add") {
        const campo = document.getElementById("campoNombre");
        if (campo) campo.focus();
    }

});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {

    // --- Establecer fecha actual ---
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById("fechaActual").value = hoy;

    // --- Validación del teléfono ---
    const tel = document.getElementById("telefono");
    tel.addEventListener("input", (e) => {
        e.target.value = e.target.value.replace(/\D/g, "").slice(0, 10);
    });

    // --- Envío con fetch ---
    document.getElementById("formComentario").addEventListener("submit", function(e) {
        e.preventDefault();

        let datos = new FormData(this);

        fetch("./home/guardar_comentario.php", {   // ✔ Mantengo tu ruta original
            method: "POST",
            body: datos
        })
        .then(r => r.json())
        .then(res => {
            alert(res.msg);
            if (res.status === "success") {
                this.reset();
                document.getElementById("fechaActual").value = hoy;
                window.location.href = "index.php#comentarios";
            }
        });
    });

});
</script>


<!-- end book section -->


  <!-- client section -->
<?php
$devs = include __DIR__ . "/home/desarrolladores.php";

?>
 <section class="client_section layout_padding-bottom">
  <div class="container">
    <div class="heading_container heading_center psudo_white_primary mb_45">
      <h2>Desarrolladores</h2>
    </div>

    <div class="carousel-wrap row">
      <div class="owl-carousel client_owl-carousel">

        <?php foreach ($devs as $dev): ?>
        <div class="item">
          <div class="box">
            <div class="detail-box">
             

              <h6>
                <?= $dev["nombre"] . " " . $dev["apellido"] ?>
              </h6>
               <p>
                <?= $dev["descripcion"] ?>
              </p>

              <p>
                <?= $dev["correo"] ?>
              </p>
            </div>

            <div class="img-box">
              <img src="<?= $dev["ruta_completa"] ?>" alt="" class="box-img">
            </div>

          </div>
        </div>
        <?php endforeach; ?>

      </div>
    </div>

  </div>
</section>

  <!-- end client section -->

  <!-- footer section -->

<?php
$footerData = include __DIR__ . '/home/footer.php';

// Seguridad: validar array
if (!is_array($footerData)) {
    $footerData = [
        "telefono"  => "Información no disponible",
        "correo"    => "Información no disponible",
        "Des_logo"  => "Información no disponible",
        "face"      => "#",
        "ins_gra"   => "#",
        "dias"      => "Información no disponible",
        "horas"     => "Información no disponible",
        "derechos"  => "Información no disponible"
    ];
}
?>


  <footer class="footer_section">
    <div class="container">
      <div class="row">
        <div class="col-md-4 footer-col">
          <div class="footer_contact">
            <h4>
              Contacto
            </h4>
            <div class="contact_link_box">
             
              <a>
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                   <?= htmlspecialchars($footerData["telefono"]) ?>
                </span>
              </a>
              <a>
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>
               <?= htmlspecialchars($footerData["correo"]) ?>
                </span>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <div class="footer_detail">
            <a  class="footer-logo">
              Uta
            </a>
            <p>
               <?= htmlspecialchars($footerData["Des_logo"]) ?>
            </p>
            <div class="footer_social">
              <a href="<?= htmlspecialchars($footerData["face"]) ?>">
              <i class="fa fa-facebook" aria-hidden="true"></i>
            </a>  
            
              <a href="<?= htmlspecialchars($footerData["ins_gra"]) ?>">
                <i class="fa fa-instagram" aria-hidden="true"></i>
              </a>

            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <h4>
            Horarios De atencion
          </h4>
           <p><?= htmlspecialchars($footerData["dias"]) ?></p>
          <p><?= htmlspecialchars($footerData["horas"]) ?></p>
        </div>
      </div>
      <div class="footer-info">
       <p>
        &copy; <span id="displayYear"></span>
        <?= htmlspecialchars($footerData["derechos"]) ?>
      </p>  
      </div>
    </div>
  </footer> 
  <!-- footer section -->

  <!-- jQery -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <!-- popper js -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
    integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
  <!-- bootstrap js -->
  <script src="js/bootstrap.js"></script>
  <!-- owl slider -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js">
  </script>
  <!-- isotope js -->
  <script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
  <!-- nice select -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
  <!-- custom js -->
  <script src="js/custom.js"></script>
  <!-- Google Map -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap">
  </script>
  <!-- End Google Map -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Modal Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">
      <div class="modal-body p-0">
        <div class="login-box">
          <img src="images/usu/logouta.jpg" class="logo-uta" alt="Logo UTA">
          <h2>login</h2>

          <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
          <?php endif; ?>
          
          <?php if (isset($_GET['error'])): ?>
            <div class="error">
              <?php 
              if ($_GET['error'] == 'usuario_no_encontrado') echo "⚠️ Usuario no encontrado.";
              elseif ($_GET['error'] == 'contraseña_incorrecta') echo "⚠️ Contraseña incorrecta.";
              elseif ($_GET['error'] == 'rol_no_valido') echo "⚠️ Rol no válido.";
              elseif ($_GET['error'] == 'usuario_no_verificado') echo "⚠️ El usuario aún no está verificado por el administrador.";

              ?>
            </div>
          <?php endif; ?>
          
          <form method="POST" action="login/validar.php">
            <div class="input-group">
              <label for="usuario">Usuario</label>
              <input type="email" name="usuario" id="usuario" placeholder="Ingrese su correo" required>
            </div>
            <div class="input-group">
              <label for="clave">Contraseña</label>
              <input type="password" name="clave" id="clave" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
          </form>
          <br>
          <div class="text-center mt-2" style="font-size: 0.9rem;">
            <a href="#" class="link-registro" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">
              ¿Olvidaste tu contraseña?
            </a>
          </div>
          <br>
          <div class="registro">
            ¿No tienes cuenta?
            <a href="Login/registrar/Registrarse.php">Registrate aquí</a>
          </div>
          <p class="nota">© Universidad Técnica de Ambato - 2025</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">
      <div class="modal-body p-0">
        <div class="login-box">
          <h2>Recuperar Contraseña</h2>
          <form id="formRequestReset">
            <p style="color: #ccc; font-size: 0.9rem;">Ingresa tu correo y te enviaremos un código de 6 dígitos.</p>
            
            <div id="msgRequestReset" class="mb-2"></div> 
            
            <div class="input-group">
              <label for="emailRequest">Correo</label>
              <input type="email" name="emailRequest" id="emailRequest" placeholder="tu_correo@uta.edu.ec" required>
            </div>
            
            <button type="submit" class="btn-login" id="btnRequestReset">Enviar Código</button>
          </form>
          <div class="text-center mt-3">
            <a href="#" class="link-registro" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
              Volver a Iniciar Sesión
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="resetCodeModal" tabindex="-1" aria-labelledby="resetCodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">
      <div class="modal-body p-0">
        <div class="login-box">
          <h2>Verificar Código</h2>
          <form id="formValidateCode">
            <p style="color: #ccc; font-size: 0.9rem;">Revisa tu correo e ingresa el código de 6 dígitos.</p>
            
            <div id="msgValidateCode" class="mb-2"></div> 
            
            <input type="hidden" name="emailValidate" id="emailValidate"> 
            
            <div class="input-group">
              <label for="resetCode">Código de 6 dígitos</label>
              <input type="text" name="resetCode" id="resetCode" maxlength="6" inputmode="numeric" required>
            </div>
            
            <button type="submit" class="btn-login" id="btnValidateCode">Verificar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="newPasswordModal" tabindex="-1" aria-labelledby="newPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content login-modal-content">
      <div class="modal-body p-0">
        <div class="login-box">
          <h2>Establecer Nueva Contraseña</h2>
          <form id="formNewPassword">
            
            <div id="msgNewPassword" class="mb-2"></div> 

            <input type="hidden" name="emailNewPass" id="emailNewPass"> 
            <input type="hidden" name="codeNewPass" id="codeNewPass"> 
            
            <div class="input-group">
              <label for="newPassword">Nueva Contraseña</label>
              <input type="password" name="newPassword" id="newPassword" placeholder="Mín. 8 caracteres" required>
            </div>
            <div class="input-group">
              <label for="confirmPassword">Confirmar Contraseña</label>
              <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Repite la contraseña" required>
            </div>

            <button type="submit" class="btn-login" id="btnNewPassword">Cambiar Contraseña</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Instancias de los Modales de Bootstrap
    const forgotModalEl = document.getElementById('forgotPasswordModal');
    const codeModalEl = document.getElementById('resetCodeModal');
    const newPassModalEl = document.getElementById('newPasswordModal');

    if (forgotModalEl) {
        const forgotModal = new bootstrap.Modal(forgotModalEl);
        const codeModal = new bootstrap.Modal(codeModalEl);
        const newPassModal = new bootstrap.Modal(newPassModalEl);
        
        // Formularios
        const formRequest = document.getElementById('formRequestReset');
        const formValidate = document.getElementById('formValidateCode');
        const formNewPass = document.getElementById('formNewPassword');
        
        // Contenedores de Mensajes
        const msgRequest = document.getElementById('msgRequestReset');
        const msgValidate = document.getElementById('msgValidateCode');
        const msgNewPass = document.getElementById('msgNewPassword');

        // Función para mostrar errores (usa tu clase .error)
        function showMessage(container, message, isSuccess = false) {
            const colorClass = isSuccess ? 'green' : ''; // Asumo 'green' para éxito
            container.innerHTML = `<div class="error" style="color:${colorClass};">${message}</div>`;
        }

        // --- Paso 1: Solicitar Código ---
        formRequest.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('emailRequest').value;
            const btn = document.getElementById('btnRequestReset');
            btn.disabled = true;
            btn.textContent = 'Enviando...';
            showMessage(msgRequest, ''); // Limpiar

            const formData = new FormData();
            formData.append('email', email);

            fetch('login/solicitar_reset.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage(msgRequest, data.msg, true);
                    document.getElementById('emailValidate').value = email; 
                    setTimeout(() => {
                        forgotModal.hide();
                        codeModal.show();
                    }, 1500);
                } else {
                    showMessage(msgRequest, data.msg, false);
                }
            })
            .catch(err => {
                showMessage(msgRequest, 'Error de conexión con el servidor.', false);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Enviar Código';
            });
        });

        // --- Paso 2: Validar Código ---
        formValidate.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnValidateCode');
            btn.disabled = true;
            btn.textContent = 'Verificando...';
            showMessage(msgValidate, '');

            const formData = new FormData(formValidate);
            const email = formData.get('emailValidate');
            const code = formData.get('resetCode');

            fetch('login/validar_codigo_reset.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('emailNewPass').value = email;
                    document.getElementById('codeNewPass').value = code;
                    codeModal.hide();
                    newPassModal.show();
                } else {
                    showMessage(msgValidate, data.msg, false);
                }
            })
            .catch(err => {
                showMessage(msgValidate, 'Error de conexión.', false);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Verificar';
            });
        });

        // --- Paso 3: Establecer Nueva Contraseña ---
        formNewPass.addEventListener('submit', function(e) {
            e.preventDefault();
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            showMessage(msgNewPass, '');

            if (newPass !== confirmPass) {
                showMessage(msgNewPass, 'Las contraseñas no coinciden.', false);
                return;
            }
            
            // (Añade tu validación de fortaleza de contraseña de JS aquí si quieres)
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@$#\-_])[A-Za-z\d!@$#\-_]{8,}$/;
            if (!regex.test(newPass)) {
                 showMessage(msgNewPass, 'La contraseña no es segura (mín 8 car, Mayús, minús, núm, símbolo !@$#_-).', false);
                 return;
            }
            
            const btn = document.getElementById('btnNewPassword');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            const formData = new FormData(formNewPass);

            fetch('login/actualizar_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showMessage(msgNewPass, data.msg, true);
                    setTimeout(() => {
                        newPassModal.hide();
                        // Opcional: mostrar modal de login
                        // const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                        // loginModal.show();
                    }, 2500);
                } else {
                    showMessage(msgNewPass, data.msg, false);
                }
            })
            .catch(err => {
                showMessage(msgNewPass, 'Error de conexión.', false);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Cambiar Contraseña';
            });
        });

        // Limpiar mensajes cuando los modales se cierran
        [forgotModalEl, codeModalEl, newPassModalEl].forEach(modalEl => {
            modalEl.addEventListener('hidden.bs.modal', function () {
                showMessage(msgRequest, '');
                showMessage(msgValidate, '');
                showMessage(msgNewPass, '');
                
                formRequest.reset();
                formValidate.reset();
                formNewPass.reset();
            });
        });
    }
});
</script>
</body>
</html>