
<?php
// Usar __DIR__ para rutas absolutas seguras
// Incluir la consulta de autoridades y otras configuraciones

$sliderData = include __DIR__ . "/home/slider.php";
$misionVision = include __DIR__ . '/home/mision_vision.php';
$footerData = include __DIR__ . '/home/footer.php';
$nosData = include __DIR__ . "/home/contactanos.php";


// Validación segura de datos
if (!is_array($sliderData)) {
    $sliderData = [
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"],
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"],
        ["titulo" => "Sin datos", "descripcion" => "No se pudo cargar información"]
    ];
}

if (!is_array($misionVision)) {
    $misionVision = [
        "mision" => "Información no disponible",
        "vision" => "Información no disponible"
    ];
}

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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTA - Portal Universitario</title>
    <link rel="stylesheet" href="css/index.css">
      <link rel="stylesheet" href="css/slider.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/estiloslogin.css"> 
  <link rel="stylesheet" href="css/filtro.css">    
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="logo">
            <h2>UTA</h2>
            <p>Universidad Técnica de Ambato</p>
        </div>

        <nav class="menu">
    <a href="index.php">Inicio</a>
    <a href="#eventosSection" class="scroll-link" data-target="eventosSection">Eventos</a>
    <a href="#comentariosSection" class="scroll-link" data-target="comentariosSection">Comentarios</a>
    <a href="contactanos.php">Contactanos</a>
</nav>

<script>
document.querySelectorAll('.scroll-link').forEach(link => {
    link.addEventListener('click', function(e) {
        
        const targetId = this.dataset.target; // eventosSection o comentariosSection

        // SI NO estamos en index.php → redirigir al index con hash
        if (!location.pathname.includes("index.php")) {
            location.href = "index.php#" + targetId;
            return;
        }

        // SI YA estamos en index.php → scroll suave
        e.preventDefault();
        const target = document.getElementById(targetId);

        if (target) {
            target.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }
    });
});
</script>

<!-- Botón login en el navbar -->
<a href="#" class="order_online" data-bs-toggle="modal" data-bs-target="#loginModal" style="color:black">login</a>
       
    </header>

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
    const loginModalEl = document.getElementById('loginModal');
if (loginModalEl) {
  loginModalEl.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const redirectPage = button?.getAttribute('data-redirect') || '';
    let form = loginModalEl.querySelector('form');
    if (form) {
      let redirectInput = form.querySelector('input[name="redirect_to"]');
      if (!redirectInput) {
        redirectInput = document.createElement('input');
        redirectInput.type = 'hidden';
        redirectInput.name = 'redirect_to';
        form.appendChild(redirectInput);
      }
      redirectInput.value = redirectPage;
    }
  });
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
  const forgotModalEl = document.getElementById('forgotPasswordModal');
  const codeModalEl = document.getElementById('resetCodeModal');
  const newPassModalEl = document.getElementById('newPasswordModal');

  if (forgotModalEl) {
    const forgotModal = new bootstrap.Modal(forgotModalEl);
    const codeModal = new bootstrap.Modal(codeModalEl);
    const newPassModal = new bootstrap.Modal(newPassModalEl);
    const formRequest = document.getElementById('formRequestReset');
    const formValidate = document.getElementById('formValidateCode');
    const formNewPass = document.getElementById('formNewPassword');
    const msgRequest = document.getElementById('msgRequestReset');
    const msgValidate = document.getElementById('msgValidateCode');
    const msgNewPass = document.getElementById('msgNewPassword');

    function showMessage(container, message, isSuccess = false) {
      const colorClass = isSuccess ? 'green' : '';
      container.innerHTML = `<div class="error" style="color:${colorClass};">${message}</div>`;
    }

    formRequest.addEventListener('submit', function(e) {
      e.preventDefault();
      const email = document.getElementById('emailRequest').value;
      const btn = document.getElementById('btnRequestReset');
      btn.disabled = true;
      btn.textContent = 'Enviando...';
      showMessage(msgRequest, '');

      const formData = new FormData();
      formData.append('email', email);

      fetch('login/solicitar_reset.php', { method: 'POST', body: formData })
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
        .catch(err => { showMessage(msgRequest, 'Error de conexión con el servidor.', false); })
        .finally(() => {
          btn.disabled = false;
          btn.textContent = 'Enviar Código';
        });
    });

    formValidate.addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = document.getElementById('btnValidateCode');
      btn.disabled = true;
      btn.textContent = 'Verificando...';
      showMessage(msgValidate, '');

      const formData = new FormData(formValidate);
      const email = formData.get('emailValidate');
      const code = formData.get('resetCode');

      fetch('login/validar_codigo_reset.php', { method: 'POST', body: formData })
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
        .catch(err => { showMessage(msgValidate, 'Error de conexión.', false); })
        .finally(() => {
          btn.disabled = false;
          btn.textContent = 'Verificar';
        });
    });

    formNewPass.addEventListener('submit', function(e) {
      e.preventDefault();
      const newPass = document.getElementById('newPassword').value;
      const confirmPass = document.getElementById('confirmPassword').value;
      showMessage(msgNewPass, '');

      if (newPass !== confirmPass) {
        showMessage(msgNewPass, 'Las contraseñas no coinciden.', false);
        return;
      }

      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@$#\-_])[A-Za-z\d!@$#\-_]{8,}$/;
      if (!regex.test(newPass)) {
        showMessage(msgNewPass, 'La contraseña no es segura (mín 8 car, Mayús, minús, núm, símbolo !@$#_-).', false);
        return;
      }

      const btn = document.getElementById('btnNewPassword');
      btn.disabled = true;
      btn.textContent = 'Guardando...';

      const formData = new FormData(formNewPass);

      fetch('login/actualizar_password.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            showMessage(msgNewPass, data.msg, true);
            setTimeout(() => { newPassModal.hide(); }, 2500);
          } else {
            showMessage(msgNewPass, data.msg, false);
          }
        })
        .catch(err => { showMessage(msgNewPass, 'Error de conexión.', false); })
        .finally(() => {
          btn.disabled = false;
          btn.textContent = 'Cambiar Contraseña';
        });
    });

    [forgotModalEl, codeModalEl, newPassModalEl].forEach(modalEl => {
      modalEl.addEventListener('hidden.bs.modal', function() {
        showMessage(msgRequest, '');
        showMessage(msgValidate, '');
        showMessage(msgNewPass, '');
        formRequest.reset();
        formValidate.reset();
        formNewPass.reset();
      });
    });

    const loginModalEl = document.getElementById('loginModal');
    if (loginModalEl) {
      loginModalEl.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const redirectPage = button?.getAttribute('data-redirect') || '';
        let form = loginModalEl.querySelector('form');
        if (form) {
          let redirectInput = form.querySelector('input[name="redirect_to"]');
          if (!redirectInput) {
            redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to';
            form.appendChild(redirectInput);
          }
          redirectInput.value = redirectPage;
        }
      });
    }
  }
});
</script>
<!-- Botón login final -->
    <!-- SLIDER -->
    <section class="slider">
        <img src="images/nosotros/uta_fisei.jpg" alt="UTA Banner">
        <div class="slider-overlay">
            <section class="slider_section ">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel" data-interval="1500">   
        <div class="carousel-inner">

          <?php foreach ($sliderData as $index => $item) : ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
              <div class="container ">
                <div class="row">
                  <div class="col-md-7 col-lg-6 ">
                    <div class="detail-box">
                      <h1><?= htmlspecialchars($item["titulo"]) ?></h1>
                      <p><?= htmlspecialchars($item["descripcion"]) ?></p>
                      <div class="btn-box">
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
        <div class="container">
          <ol class="carousel-indicators">
            <?php foreach ($sliderData as $index => $item) : ?>
              <li data-target="#customCarousel1" data-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>"></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </section>
        </div>
    </section>



    <!-- nosotros -->
 <section class="nosotros-section">
    <div class="nosotros-contenido">
        
        <!-- Imagen -->
        <div class="nosotros-imagen">
            <img src="images/nosotros/<?= htmlspecialchars($nosData["ruta"]) ?>" alt="Nosotros - UTA">
        </div>

        <!-- Texto -->
        <div class="nosotros-texto">
            <h2>Sobre Nosotros</h2>
          <p><?= nl2br(htmlspecialchars($nosData["des_noso"])) ?></p>
            <a href="<?= htmlspecialchars($nosData["link_noso"]) ?>" class="btn-nosotros">Conocer más</a>
        </div>

    </div>
</section>
<style>
    /* ================================
   VARIABLES GLOBALES PARA COLORES
================================ */
:root {
    --rojo-oscuro:  #490505ff;; /* Rojo oscuro base */
    --rojo-claro-hover: #DC143C; /* Rojo más claro solo para hovers en botones */
    --blanco: #ffffff; /* Blanco puro */
    --gris-claro: #f5f5f5; /* Fondo suave */
    --gris-medio: #cccccc; /* Bordes y texto secundario */
    --gris-oscuro: #333333; /* Texto principal */
    --sombra: rgba(0, 0, 0, 0.1); /* Sombras suaves */
}

/* ================================
   RESET Y BASE GENERAL
================================ */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif; /* Fuente moderna y legible */
    line-height: 1.6;
    color: var(--gris-oscuro);
    background-color: var(--gris-claro);
    overflow-x: hidden; /* Evitar scroll horizontal */
}

/* ================================
   NAVBAR
================================ */
.navbar {
    background-color: var(--rojo-oscuro);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 10px var(--sombra);
}

.navbar .logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar .logo h2 {
    color: var(--blanco);
    font-size: 24px;
    font-weight: 700;
}

.navbar .logo p {
    color: var(--blanco);
    font-size: 14px;
    opacity: 0.9;
}

.navbar .menu {
    display: flex;
    gap: 20px;
}

.navbar .menu a {
    color: var(--blanco);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.navbar .menu a:hover {
    color: var(--gris-claro);
}

.navbar .order_online {
    background-color: var(--blanco);
    color: var(--rojo-oscuro);
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
    text-decoration: none;
}

.navbar .order_online:hover {
    background-color: var(--gris-claro);
}

/* ================================
   SLIDER PRINCIPAL
================================ */
.slider {
    position: relative;
    height: 420px;
    overflow: hidden;
    margin-top: 80px; /* Espacio para navbar fija */
}

.slider img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.45);
}

.slider-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--blanco);
}

.slider .detail-box {
    text-align: center;
    max-width: 600px;
    padding: 20px;
}

.slider .detail-box h1 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 15px;
    text-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
}

.slider .detail-box p {
    font-size: 18px;
    margin-bottom: 20px;
    opacity: 0.9;
}

/* ================================
   SECCIÓN NOSOTROS
================================ */
.nosotros-section {
    width: 100%;
    padding: 60px 10%;
    background: var(--gris-claro);
    display: flex;
    justify-content: center;
    align-items: center;
}

.nosotros-contenido {
    display: flex;
    align-items: center;
    gap: 40px;
    max-width: 1200px;
    animation: fadeIn 0.8s ease-in-out;
}

.nosotros-imagen img {
    width: 450px;
    height: auto;
    border-radius: 15px;
    object-fit: cover;
    box-shadow: 0 5px 20px var(--sombra);
}

.nosotros-texto {
    flex: 1;
}

.nosotros-texto h2 {
    font-size: 2.3rem;
    margin-bottom: 15px;
    color: var(--rojo-oscuro);
    font-weight: 700;
    border-left: 5px solid var(--rojo-oscuro);
    padding-left: 12px;
}

.nosotros-texto p {
    font-size: 1.1rem;
    color: var(--gris-oscuro);
    line-height: 1.7;
    margin-bottom: 15px;
}

.btn-nosotros {
    display: inline-block;
    padding: 10px 20px;
    background: var(--rojo-oscuro);
    color: var(--blanco);
    border-radius: 8px;
    font-size: 1rem;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn-nosotros:hover {
    background: var(--rojo-claro-hover);
    transform: translateY(-3px);
}

/* ================================
   SECCIÓN UBICACIÓN
================================ */
.ubicacion-section {
    padding: 60px 20px;
    background: var(--blanco);
    text-align: center;
}

.ubicacion-content h2 {
    font-size: 32px;
    margin-bottom: 10px;
    color: var(--rojo-oscuro);
}

.ubicacion-content p {
    font-size: 18px;
    color: var(--gris-medio);
    margin-bottom: 30px;
}

.ubicacion-mapa {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 15px;
    box-shadow: 0 4px 20px var(--sombra);
}

.ubicacion-mapa iframe {
    width: 100%;
    height: 450px;
    border: 0;
}

/* ================================
   SECCIÓN DE EVENTOS (RECOMENDADOS Y FILTROS)
================================ */
#seccionRecomendados {
    padding: 50px 20px;
    background-color: var(--blanco);
    margin-bottom: 40px;
}

.titulo-seccion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.titulo-eventos {
    font-size: 28px;
    font-weight: 700;
    color: var(--rojo-oscuro);
    text-align: center;
    flex: 1;
}

.btn-ver-todo {
    background-color: var(--rojo-oscuro);
    color: var(--blanco);
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-ver-todo:hover {
    background-color: var(--rojo-claro-hover);
}

.slider-container {
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
}

.slider-favoritos {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 10px;
    width: 100%;
    scrollbar-width: none;
}

.slider-favoritos::-webkit-scrollbar {
    display: none;
}

.arrow {
    background-color: var(--rojo-oscuro);
    border: none;
    color: var(--blanco);
    font-size: 28px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
}

.arrow:hover {
    background-color: var(--rojo-claro-hover);
}

.tarjeta-evento {
    min-width: 300px;
    background-color: var(--blanco);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 12px var(--sombra);
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.tarjeta-evento:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px var(--sombra);
}

.tarjeta-evento img {
    width: 100%;
    height: 170px;
    object-fit: cover;
    border-radius: 12px;
}

.tarjeta-evento .titulo-evento {
    font-size: 19px;
    font-weight: 700;
    text-align: center;
    color: var(--gris-oscuro);
}

.tarjeta-evento .descripcion-evento {
    font-size: 14px;
    text-align: center;
    color: var(--gris-medio);
}

.tarjeta-evento .fecha-evento {
    text-align: center;
    font-size: 14px;
    color: var(--gris-oscuro);
}

.btn-inscribirse {
    background-color: var(--rojo-oscuro);
    border-color: var(--rojo-oscuro);
    color: var(--blanco);
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 10px 20px;
    border-radius: 8px;
}

.btn-inscribirse:hover {
    background-color: var(--rojo-claro-hover);
    border-color: var(--rojo-claro-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px var(--sombra);
}

/* ================================
   MENÚ DE FILTROS
================================ */
#menuFiltros {
    padding: 50px 20px;
    background-color: var(--gris-claro);
}

#menuFiltrosContainer {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-bottom: 30px;
}

.btnFiltro {
    background-color: var(--rojo-oscuro);
    border: none;
    padding: 10px 18px;
    color: var(--blanco);
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    transition: background-color 0.3s ease;
}

.btnFiltro:hover,
.btnFiltro.activo {
    background-color: var(--rojo-claro-hover);
}

#contenedorTarjetas {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    padding: 20px 0;
}

/* ================================
   AUTORIDADES
================================ */
.autoridades {
    padding: 50px 20px;
    background-color: var(--gris-claro);
    text-align: center;
}

.titulo-autoridades {
    font-size: 32px;
    font-weight: 800;
    color: var(--rojo-oscuro);
    margin-bottom: 30px;
    position: relative;
}

.titulo-autoridades::after {
    content: "";
    display: block;
    width: 90px;
    height: 4px;
    background-color: var(--rojo-oscuro);
    margin: 12px auto 0;
    border-radius: 2px;
}

.slider-autoridades {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 15px;
    scrollbar-width: none;
}

.slider-autoridades::-webkit-scrollbar {
    display: none;
}

.autoridad-card {
    min-width: 220px;
    background-color: var(--blanco);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 15px var(--sombra);
    transition: transform 0.3s ease;
    text-align: center;
}

.autoridad-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px var(--sombra);
}

.autoridad-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 15px;
}

.autoridad-card h3 {
    font-size: 18px;
    color: var(--gris-oscuro);
    margin-bottom: 10px;
}

.autoridad-card p {
    font-size: 15px;
    color: var(--gris-medio);
    margin-bottom: 15px;
}

.btn-detalles {
    background-color: var(--rojo-oscuro);
    color: var(--blanco);
    border: none;
    padding: 10px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.btn-detalles:hover {
    background-color: var(--rojo-claro-hover);
}

/* ================================
   COMENTARIOS
================================ */
.comentarios-section {
    padding: 50px 20px;
    background-color: var(--blanco);
    text-align: center;
}

.comentarios-section h2 {
    font-size: 28px;
    color: var(--rojo-oscuro);
    margin-bottom: 30px;
}

.comentarios-lista {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    margin-bottom: 30px;
}

.comentario {
    background-color: var(--gris-claro);
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 12px var(--sombra);
    max-width: 100000px; /* Manteniendo el largo como pediste */
    text-align: left;
}

.comentario h3 {
    color: var(--rojo-oscuro);
    margin-bottom: 10px;
}

.comentario p {
    color: var(--gris-oscuro);
    margin-bottom: 10px;
}

.comentario small {
    color: var(--gris-medio);
}

.comments-btn {
    background-color: var(--rojo-oscuro);
    color: var(--blanco);
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.comments-btn:hover {
    background-color: var(--rojo-claro-hover);
}

/* ================================
   FOOTER
================================ */
.uta-footer {
    background-color: var(--rojo-oscuro);
    color: var(--blanco);
    padding: 40px 20px 20px;
    text-align: center;
}

.uta-footer-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 20px;
}

.footer-col {
    flex: 1;
    min-width: 200px;
}

.footer-col h3 {
    font-size: 18px;
    margin-bottom: 15px;
}

.footer-col p,
.footer-col li {
    margin-bottom: 8px;
}

.social-icons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 15px;
}

.social-icons img {
    width: 30px;
    height: 30px;
    transition: transform 0.3s ease;
}

.social-icons img:hover {
    transform: scale(1.1);
}

.uta-footer-bottom {
    border-top: 1px solid var(--gris-medio);
    padding-top: 15px;
    font-size: 14px;
}
.uta-footer-bottom a {
    color: var(--blanco);
    text-decoration: none;
    padding-left: 10px;
    margin: 0 5px;
}
.uta-footer-bottom i {
    color: yellow;
    padding: 0 5px;
}

/* ================================
   RESPONSIVIDAD
================================ */
@media (max-width: 768px) {
    .navbar .menu {
        display: none; /* Podrías agregar un menú móvil */
    }

    .nosotros-contenido {
        flex-direction: column;
        text-align: center;
    }

    .nosotros-imagen img {
        width: 90%;
    }

    .nosotros-texto h2 {
        text-align: center;
        border-left: none;
        padding-left: 0;
    }

    .ubicacion-content h2 {
        font-size: 26px;
    }

    .ubicacion-mapa iframe {
        height: 350px;
    }

    .mision-vision {
        flex-direction: column;
        gap: 20px;
    }

    .slider-container {
        flex-direction: column;
        gap: 10px;
    }

    .arrow {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }

    .tarjeta-evento {
        min-width: 250px;
    }

    .autoridad-card {
        min-width: 180px;
    }

    .comentarios-lista {
        flex-direction: column;
        align-items: center;
    }
}

/* ================================
   ANIMACIONES ADICIONALES
================================ */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
<style>
/* ====== SECCIÓN NOSOTROS ====== */
.nosotros-section {
    width: 100%;
    padding: 60px 10%;
    background: #f5f6fa;
    display: flex;
    justify-content: center;
    align-items: center;
}

.nosotros-contenido {
    display: flex;
    align-items: center;
    gap: 40px;
    max-width: 1200px;
    animation: fadeIn 0.8s ease-in-out;
}

/* Imagen */
.nosotros-imagen img {
    width: 450px;
    height: auto;
    border-radius: 15px;
    object-fit: cover;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

/* Texto */
.nosotros-texto {
    flex: 1;
}

.nosotros-texto h2 {
    font-size: 2.3rem;
    margin-bottom: 15px;
    color:  #640c0cff;
    font-weight: 700;
    border-left: 5px solid  #640c0cff;
    padding-left: 12px;
}

.nosotros-texto p {
    font-size: 1.1rem;
    color: #444;
    line-height: 1.7;
    margin-bottom: 15px;
}

/* Botón */
.btn-nosotros {
    display: inline-block;
    padding: 10px 20px;
    background:  #640c0cff;
    color: white;
    border-radius: 8px;
    font-size: 1rem;
    text-decoration: none;
    transition: 0.3s;
}

.btn-nosotros:hover {
    background:  #683939ff;
    transform: translateY(-3px);
}

/* Animación fade-in */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .nosotros-contenido {
        flex-direction: column;
        text-align: center;
    }

    .nosotros-imagen img {
        width: 90%;
    }

    .nosotros-texto h2 {
        text-align: center;
        border-left: none;
        padding-left: 0;
    }
}


</style>

  <!-- nosotros -->

<!-- ubicacion -->
<section class="ubicacion-section" id="ubicacionSection">
    <div class="ubicacion-content">
        <h2>📍 Nuestra Ubicación</h2>
        <p>Encuéntranos fácilmente en el mapa a continuación.</p>
    </div>

    <div class="ubicacion-mapa">
        <!-- Aquí se carga el mapa desde la base -->
        <?php 
            // Ejemplo: $ubicacion contiene el iframe cargado desde la BD
            // echo $ubicacion;
        ?>

        <!-- TEMPORAL: Ejemplo de prueba -->
       <?php
            // Se utiliza el array ya cargado $nosData
            if (!empty($nosData["maps"])) {
              // Permitir la inserción del iframe sin sanitizar si se confía en la fuente (BD)
              // ADVERTENCIA: Esta es una excepción. Si los datos no vienen de una fuente segura, esto es un riesgo XSS.
              echo $nosData["maps"];
            } else {
              echo "<p>No hay un mapa configurado.</p>";
            }
            ?>
    </div>
</section>
<style>
    .ubicacion-section {
    padding: 60px 20px;
    background: #f5f5f5;
    text-align: center;
}

.ubicacion-content h2 {
    font-size: 32px;
    margin-bottom: 10px;
    color: #222;
}

.ubicacion-content p {
    font-size: 18px;
    color: #555;
    margin-bottom: 30px;
}

/* Contenedor del mapa */
.ubicacion-mapa {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

/* Ajuste del iframe */
.ubicacion-mapa iframe {
    width: 100%;
    height: 450px;
    border: 0;
}

/* Responsive */
@media (max-width: 600px) {
    .ubicacion-content h2 {
        font-size: 26px;
    }
    .ubicacion-mapa iframe {
        height: 350px;
    }
}

    </style>

<!-- ubicacion -->
<style>

 /* ================================
   SECCIÓN RECOMENDADOS - ESTILOS
================================ */

/* CONTENEDOR PRINCIPAL */
#seccionRecomendados {
    margin-top: 40px;
    width: 100%;
}

/* TÍTULO */
#seccionRecomendados .titulo-eventos {
    margin: 0;
}

/* SLIDER CONTENEDOR */
#seccionRecomendados .slider-container {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    padding: 15px 0;
}

/* BOTONES FLECHA */
#seccionRecomendados .arrow {
    background: #a30000;
    border: none;
    color: white;
    font-size: 28px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    cursor: pointer;
    transition: 0.25s ease;
    display: flex;
    justify-content: center;
    align-items: center;
}

#seccionRecomendados .arrow:hover {
    background: #7a0000;
    transform: scale(1.1);
}

#seccionRecomendados .arrow:active {
    transform: scale(0.9);
}

/* ÁREA donde van las tarjetas */
#seccionRecomendados .slider-favoritos {
    display: flex;
    gap: 25px;
    overflow: hidden;
    scroll-behavior: smooth;
    padding: 10px;
    width: 100%;
}

/* TARJETAS dentro del slider */
#seccionRecomendados .tarjeta-evento {
    min-width: 300px;
    background: white;
    border-radius: 15px;
    padding: 15px;
    border: 1px solid #ddd;
    box-shadow: 0 4px 12px rgba(0,0,0,0.10);
    transition: 0.25s ease;
    display: flex;
    flex-direction: column;
}

#seccionRecomendados .tarjeta-evento:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}

/* IMAGEN */
#seccionRecomendados .tarjeta-evento img {
    width: 100%;
    height: 170px;
    object-fit: cover;
    border-radius: 12px;
    background: #f6f6f6;
}

/* TÍTULO DEL EVENTO */
#seccionRecomendados .tarjeta-evento .titulo-evento {
    font-size: 19px;
    font-weight: 700;
    text-align: center;
    margin-top: 15px;
}

/* DESCRIPCIÓN */
#seccionRecomendados .tarjeta-evento .descripcion-evento {
    font-size: 14px;
    text-align: center;
    color: #555;
    margin-top: 10px;
}

/* FECHA */
#seccionRecomendados .tarjeta-evento .fecha-evento {
    margin-top: 10px;
    text-align: center;
    font-size: 14px;
    color: #444;
}

/* BOTÓN FAVORITO */
#seccionRecomendados .tarjeta-evento .btn-fav {
    margin-top: 12px;
    padding: 10px;
    border-radius: 10px;
    border: none;
    background: #ffe6ec;
    color: #c40030;
    cursor: pointer;
    transition: 0.2s;
    font-size: 16px;
}

#seccionRecomendados .tarjeta-evento .btn-fav:hover {
    background: #ffbfd0;
}

/* BOTÓN FAVORITO - ACTIVO */
#seccionRecomendados .tarjeta-evento .btn-fav.active {
    background: #ff224d;
    color: #fff;
    transform: scale(1.05);
}

/* Fondo de la foto */
.slider img {
    width: 100%;
    height: 420px;
    object-fit: cover;
    filter: brightness(0.45); /* oscurece la imagen para mejorar el texto */
}

/* Capa oscura encima de la imagen */
.slider-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 420px;
    display: flex;
    align-items: center;
    z-index: 10;
}

/* Contenido del texto */
.slider .detail-box {
    color: white;
    padding: 20px;
    max-width: 600px;
    animation: fadeInUp 0.8s ease-out;
}

/* Títulos bonitos */
.slider .detail-box h1 {
    font-size: 42px;
    font-weight: 800;
    text-shadow: 0 3px 10px rgba(0,0,0,0.4);
}

/* Descripción */
.slider .detail-box p {
    font-size: 18px;
    line-height: 1.4;
    margin-top: 10px;
    opacity: 0.9;
}

/* Indicadores abajo */
#customCarousel1 .carousel-indicators li {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: white;
    opacity: 0.6;
}

#customCarousel1 .carousel-indicators .active {
    background-color: #a30000;
    opacity: 1;
}

/* Animación suave */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
/* ❌ OCULTAR indicadores numéricos generados por Bootstrap 5 */
.carousel-indicators [data-bs-target] {
    display: none !important;
}

/* ✔ Usar solo los puntos clásicos de Bootstrap 4 */
.carousel-indicators li {
    width: 12px;
    height: 12px;
    background-color: white;
    border: 2px solid #a90000;
    border-radius: 50%;
    margin: 5px;
    cursor: pointer;
    opacity: 0.6;
    transition: 0.3s;
}

.carousel-indicators .active {
    background-color: #a90000;
    opacity: 1;
}

/* ✔ Colocarlos más arriba para evitar solapamiento con números ocultos */
.carousel-indicators {
    bottom: 20px !important;
}

    </style>
<script>

    // 📌 Donde se guardan los eventos (solo para tarjetas nuevas)
let eventosFavoritosNuevos = [];
let eventosGeneralesNuevos = [];


// ======================================================
// 🔥 1. Mostrar tarjetas SOLO de FAVORITOS
// ======================================================
function generarTarjetasFavoritos() {

    fetch("./login/registrar/eventos_favoritos.php")
        .then(r => r.json())
        .then(data => {
            eventosFavoritosNuevos = data;
            dibujarTarjetas(eventosFavoritosNuevos);
        });
}


// ======================================================
// 🔥 2. Mostrar tarjetas de TODOS los eventos
// ======================================================
function generarTarjetasGeneral() {

    fetch("./login/registrar/eventos.php")
        .then(r => r.json())
        .then(data => {
            eventosGeneralesNuevos = data;
            dibujarTarjetas(eventosGeneralesNuevos);
        });
}
    function generarTarjetasSinCarrera() {

            // Si todavía no se cargan los eventos generales → cargar y luego filtrar
        

                fetch("./login/registrar/eventos.php")
                    .then(r => r.json())
                    .then(data => {
                        eventosGeneralesNuevos = data;

                        // FILTRAR AQUÍ
                        const sinCarrera = eventosGeneralesNuevos.filter(e => e.id_carrera == 0);

                        // Dibujar tarjetas filtradas
                        dibujarTarjetas(sinCarrera);
                    });


        }
function filtrarCarrera(idCarrera) {

    // Si todavía no están cargados los eventos generales → cargar y luego filtrar
    if (eventosGeneralesNuevos.length === 0) {

        fetch("./login/registrar/eventos.php")
            .then(r => r.json())
            .then(data => {
                eventosGeneralesNuevos = data;

                const filtrados = eventosGeneralesNuevos.filter(e => e.id_carrera == idCarrera);
                dibujarTarjetas(filtrados);
            });

    } else {

        // Si ya están cargados → filtrar directamente
        const filtrados = eventosGeneralesNuevos.filter(e => e.id_carrera == idCarrera);
        dibujarTarjetas(filtrados);
    }
}





// ======================================================
// 🎨 3. Dibujar tarjetas genérico (reutilizable)
// ======================================================
function dibujarTarjetas(lista) {

    let cont = document.getElementById("contenedorTarjetas");
    cont.innerHTML = ""; // limpiar

    if (!lista || lista.length === 0) {
        cont.innerHTML = "<p>No hay eventos disponibles.</p>";
        return;
    }

    lista.forEach(e => {

        cont.innerHTML += `
            <div class="tarjeta-evento">
                
                <img src="${e.imagen ?? 'img/noimage.png'}" 
                     alt="imagen del evento">

                <div class="titulo-evento">${e.nombre}</div>

                <div class="descripcion-evento">
                    ${e.descripcion}
                </div>

                <div class="fecha-evento">
                    ${e.fecha}
                </div>

              <button class="btn btn-inscribirse btn-sm mt-2"
    data-bs-toggle="modal"
    data-bs-target="#loginModal"
    data-redirect="buscar_eventos.php">
    Inscribirse
</button>
            </div>
        `;
    });
}

</script>
<style>
    /* Estilo del botón Inscribirse */
.btn-inscribirse {
    background-color: #9a0918ff; /* Rojo Bootstrap */
    border-color: #dc3545;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 0.5rem 1.5rem;
    border-radius: 0.375rem;
}

.btn-inscribirse:hover {
    background-color: #bb2d3b; /* Rojo más oscuro al hover */
    border-color: #b02a37;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-inscribirse:active {
    background-color: #b02a37;
    border-color: #a52834;
    transform: translateY(0);
}

.btn-inscribirse:focus {
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.5);
}
    </style>
<style>


    #contenedorTarjetas {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    width: 100%;
    padding: 20px 0;
    box-sizing: border-box;
}
.tarjeta-evento {
    width: 290px;
    background: #fff;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0px 4px 18px rgba(0,0,0,0.12);
    transition: 0.25s ease;
    display: flex;
    flex-direction: column;
}


    .contenedor-tarjetas {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    margin-top: 30px;
}

.tarjeta-evento {
    width: 290px;
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    transition: 0.25s;
}

.tarjeta-evento:hover {
    transform: translateY(-4px);
    box-shadow: 0px 6px 22px rgba(0,0,0,0.18);
}


.tarjeta-evento img {
    width: 100%;
    height: 170px;
    object-fit: cover;
    border-radius: 8px;
}

.titulo-evento {
    font-size: 20px;
    font-weight: 700;
    margin: 12px 0 5px 0;
    text-align: center;
}


.descripcion-evento {
    text-align: center;
    color: #555;
    margin-top: 5px;

    /* Control para textos largos */
    max-height: 110px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fecha-evento {
    text-align: center;
    margin-top: 10px;
    font-size: 14px;
    color: #333;
}


.btn-fav {
    width: 100%;
    margin-top: auto;
    border: 1px solid #444;
    background: white;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s;
}

.btn-fav:hover {
    background: #eeeeee;
}

/* Contenedor del menú */
.titulo-eventos {
    text-align: center;
    font-size: 34px;
    font-weight: 800;
    margin-top: 40px;
    margin-bottom: 20px;
    color: #2e2e2e;
}

.titulo-eventos::after {
    content: "";
    width: 90px;
    height: 4px;
    background: #b30000;
    display: block;
    margin: 12px auto 0 auto;
    border-radius: 6px;
}
    

/* CONTENEDOR PRINCIPAL PARA TODOS LOS BOTONES */
#menuFiltrosContainer {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin: 20px auto;
    width: 100%;
}

/* ESTILO DE BOTONES (FIJOS + DINÁMICOS) */
.btnFiltro,
#menuCarreras button {
    background: #861a10ff;
    border: none;
    padding: 10px 18px;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    transition: 0.2s;
    white-space: nowrap;
}

.btnFiltro:hover,
#menuCarreras button:hover {
    background: #4e555b;
    transform: scale(1.05);
}

/* BOTÓN ACTIVO */
.activo {
    background: #1f085cff !important;
    font-weight: bold;
    transform: scale(1.05);
}
@media (max-width: 600px) {
    .tarjeta-evento {
        width: 90%;
    }

    .descripcion-evento {
        max-height: none; /* mostrar todo en móvil */
    }
}


/* Ocultar completamente los indicadores generados por Bootstrap 5 */
.carousel-indicators button,
.carousel-indicators [data-bs-target],
.carousel-indicators .active[data-bs-target] {
    display: none !important;
}


</style>

    <!-- SECCIÓN DE TARJETAS -->

   <script>     
function verTodo() {
    document.getElementById("seccionRecomendados").style.display = "none"; // oculta todo el bloque
    document.getElementById("menuFiltros").style.display = "block";

    mostrarEventos(eventosGlobales);
    cargarCarrerasMenu();
}
function fintodo(){
verTodo();
generarTarjetasFavoritos();

}


// =============================
//     FUNCIÓN mostrarEventos
// =============================
function mostrarEventos(lista) {
    const contenedor = document.getElementById("sliderFavoritos");

    if (!contenedor) {
        console.error("❌ No existe el contenedor #sliderFavoritos");
        return;
    }

    if (!lista || lista.length === 0) {
        contenedor.innerHTML = "<p>No hay eventos disponibles.</p>";
        return;
    }

    let html = "";
    lista.forEach(ev => {
        html += `
            <div class="card card-evento">
                <img src="${ev.imagen}" alt="">
                <h4>${ev.nombre}</h4>
                <p>${ev.descripcion}</p>
                <p>${ev.fecha}</p>
               
              <button class="btn btn-inscribirse btn-sm mt-2"
    data-bs-toggle="modal"
    data-bs-target="#loginModal"
    data-redirect="buscar_eventos.php">
    Inscribirse
</button>
            </div>
        `;
    });

    contenedor.innerHTML = html;
}


// =============================
//     MOVER MANUAL POR FLECHAS
// =============================
function moverSlider(direccion) {
    const slider = document.getElementById("sliderFavoritos");
    slider.scrollLeft += direccion * 300;
}


// =============================
//   CARGAR FAVORITOS DEL SLIDER
// =============================
function cargarSliderFavoritos() {
    let favoritos = JSON.parse(localStorage.getItem("favoritos")) || [];

    if (favoritos.length === 0) {
        document.getElementById("sliderFavoritos").innerHTML = `
            <p class="text-center w-100">No tienes eventos favoritos aún.</p>
        `;
        return;
    }

    let eventosFavoritos = eventosGlobales.filter(ev => favoritos.includes(ev.id));

    let html = "";
    eventosFavoritos.forEach(ev => {
        html += `
            <div class="card card-fav">
                <img src="${ev.imagen}" class="card-img-top" alt="img">
                <div class="card-body">
                    <h5>${ev.nombre}</h5>
                    <p>${ev.fecha}</p>
                    <button onclick="toggleFavorito(${ev.id})" class="btn btn-danger">Quitar ❤️</button>
                </div>
            </div>
        `;
    });

    document.getElementById("sliderFavoritos").innerHTML = html;
}
function cargarCarrerasMenu() {
fetch("./login/registrar/car.php")
    .then(r => r.json())
    .then(data => {
        let html = "";

        data.carreras.forEach(c => {   // ✅ AQUÍ SÍ ES CORRECTO
            html += `
                <button class="btn btn-secondary" onclick="filtrarCarrera(${c.id})">
                    ${c.nombre}
                </button>
            `;
        });

        document.getElementById("menuCarreras").innerHTML = html;
    });

}
// =============================
//    CARGAR EVENTOS DESDE PHP
// =============================
let eventosGlobales = [];

fetch("./login/registrar/eventos_favoritos.php")
    .then(r => r.json())
    .then(data => {
        eventosGlobales = data;
        cargarSliderFavoritos();
        mostrarEventos(eventosGlobales);
        iniciarAutoScroll();   // 🔥 activar movimiento automático
    })
    .catch(err => console.error("Error cargando eventos:", err));
// =====================================================
//      🔥 MOVIMIENTO AUTOMÁTICO SUAVE DEL SLIDER
// =====================================================

let autoScrollActivo = true;

function iniciarAutoScroll() {
    const slider = document.getElementById("sliderFavoritos");
    if (!slider) return;

    function mover() {
        if (!autoScrollActivo) return;

        slider.scrollLeft += 1;   // velocidad suave

        // si llega al final vuelve al inicio
        if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 5) {
            slider.scrollLeft = 0;
        }

        requestAnimationFrame(mover);
    }

    mover();

    // detener cuando el usuario pasa el mouse
    slider.addEventListener("mouseenter", () => autoScrollActivo = false);
    slider.addEventListener("mouseleave", () => autoScrollActivo = true);
}

</script>



<style>
/* Fondo oscuro */
.comment-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fadeIn .3s ease;
}

/* Caja */
.comment-modal {
    background: #ffffff;
    padding: 25px;
    width: 360px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    animation: slideIn .25s ease;
    position: relative;
}

.comment-modal h2 {
    margin-bottom: 15px;
    font-size: 1.5em;
    text-align: center;
}

/* Inputs */
.comment-modal input,
.comment-modal textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

/* Botón */
.send-btn {
    width: 100%;
    background: #0066cc;
    border: none;
    padding: 10px;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
}

.send-btn:hover {
    background: #004c99;
}

/* Cerrar */
.modal-close {
    position: absolute;
    right: 12px;
    top: 8px;
    font-size: 25px;
    cursor: pointer;
}

/* Mensajes */
.msg {
    text-align: center;
    margin-top: 10px;
    font-weight: bold;
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity:0; }
    to { opacity:1; }
}

@keyframes slideIn {
    from { transform: scale(0.8); opacity:0; }
    to { transform: scale(1); opacity:1; }
}


</style>
    




<!-- AUTORIDADES (SLIDER) -->


<script>
document.addEventListener("DOMContentLoaded", () => {

    fetch("home/autoridad2.php")
        .then(r => r.json())
        .then(autoridades => {
            let slider = document.getElementById("sliderAutoridades");
            slider.innerHTML = "";

            if (autoridades.length === 0) {
                slider.innerHTML = "<p>No hay autoridades registradas.</p>";
                return;
            }

            let modalsHTML = "";

            autoridades.forEach(a => {
                let id = a.id;
                let modalId = "autoridadModal_" + id;

                let foto = a.foto || "images/placeholder_default.png";
                let cargo = a.cargo || "Autoridad";
                let encabezado = `${a.titulo ?? ""} ${a.nombre ?? ""}`.trim();

                slider.innerHTML += `
                    <div class="autoridad autoridad-card">
                        <img src="${foto}" alt="${encabezado}">
                        <h3>${encabezado}</h3>
                        <p>${cargo}</p>
                        <button class="btn-detalles" data-bs-toggle="modal" data-bs-target="#${modalId}">
                            Más detalles
                        </button>
                    </div>
                `;

                modalsHTML += `
                    <div class="modal fade" id="${modalId}" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">${encabezado}</h5>
                          
                          </div>
                          <div class="modal-body">
                            ${a.resumen ? `<p>${a.resumen.replace(/\n/g, "<br>")}</p>` : ""}
                            <ul>
                              ${a.direccion ? `<li><strong>Dirección:</strong> ${a.direccion}</li>` : ""}
                              ${a.telefono ? `<li><strong>Teléfono:</strong> ${a.telefono}${a.telefono_ext ? " ext " + a.telefono_ext : ""}</li>` : ""}
                              ${a.horario ? `<li><strong>Horario:</strong> ${a.horario.replace(/\n/g, "<br>")}</li>` : ""}
                            </ul>
                          </div>
                          <div class="modal-footer">
                            ${a.email ? `<a href="mailto:${a.email}" class="btn btn-secondary">Contactar</a>` : ""}
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>
                `;
            });

            document.getElementById("modals-root").insertAdjacentHTML("beforeend", modalsHTML);


            setTimeout(inicializarSlider, 200);
        })
        .catch(err => {
            console.error("Error al cargar autoridades:", err);
            document.getElementById("sliderAutoridades").innerHTML = "Error cargando autoridades";
        });
});
</script>




  
    
 <!-- Slider Autoridades -->
 <script>
    // === SLIDER AUTORIDADES ===
    function inicializarSlider() {
        const slider = document.querySelector(".slider-autoridades")
        if (!slider || slider.children.length === 0) {
            console.warn("Slider vacío, no se puede iniciar.");
            return;
        }

        // Detectar ancho real de cada tarjeta (incluye padding y espacio)
        const cardWidth = slider.children[0].offsetWidth + 30;

        window.moveRight = function () {
            slider.scrollBy({ left: cardWidth, behavior: 'smooth' });
        };

        window.moveLeft = function () {
            slider.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        };

       function autoSlide() {
    slider.scrollBy({ left: 2, behavior: 'smooth' });

    // Reinicia cuando llega al final
    if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 5) {
        slider.scrollTo({ left: 0 });
    }
}
       let auto = setInterval(autoSlide, 20); // Movimiento suave continuo
        slider.addEventListener("mouseenter", () => clearInterval(auto));
        slider.addEventListener("mouseleave", () => auto = setInterval(autoSlide, 3000));
    }
</script>
    <div id="modals-root"></div>


</section>
<script>
document.addEventListener("DOMContentLoaded", () => {

    function htmlspecialchars(str) {
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;");
    }

    // === CARGAR COMENTARIOS DE LA BASE ===
    fetch("./home/obtener_comentarios.php")
        .then(res => res.json())
        .then(data => {
            const lista = document.getElementById("comentariosLista");

            if (!data || data.length === 0) {
                lista.innerHTML = "<p>No hay comentarios aún.</p>";
                return;
            }

            let html = "";
            data.forEach(c => {
                const nombre = c.nombre ? htmlspecialchars(c.nombre) : "Anónimo";
                const comentario = c.comentario ? htmlspecialchars(c.comentario) : "";
                const fecha = c.fecha ? htmlspecialchars(c.fecha) : "";

                html += `
                    <div class="comentario">
                        <h3>${nombre}</h3>
                        <p>"${comentario}"</p>
                        <small>Publicado el ${fecha}</small>
                    </div>
                `;
            });

            lista.innerHTML = html;
        })
        .catch(err => console.error("Error cargando comentarios:", err));

});
</script>
<!-- 📌 Contenedor global donde se inyectarán todos los modals -->
  <!-- FOOTER -->
<footer class="uta-footer">
    <div class="uta-footer-container">

        <!-- CONTACTO -->
        <div class="footer-col">
            <h3>Contacto</h3>

            <p>📍 Ambato - Ecuador</p>
            <p>📞 <?= htmlspecialchars($footerData["telefono"]) ?></p>
            <p>📧 <?= htmlspecialchars($footerData["correo"]) ?></p>
        </div>

        <!-- INFORMACIÓN -->
        <div class="footer-col">
            <h3>Información</h3>
            <ul>
                <li>Sobre la Universidad</li>
                <li>Carreras</li>
                <li>Servicios</li>
            </ul>
        </div>

        <!-- HORARIOS -->
        <div class="footer-col">
            <h3>Horarios de Atención</h3>
            <ul>
                <li><?= htmlspecialchars($footerData["dias"]) ?></li>
                <li><?= htmlspecialchars($footerData["horas"]) ?></li>
            </ul>
        </div>

        <!-- CENTRO UTA -->
        <div class="footer-col logo-center">
            <h2>UTA</h2>
            <p>Universidad para tu futuro</p>

            <div class="social-icons">
    <a href="<?= htmlspecialchars($footerData["face"]) ?>">
        <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
    </a>

    <a href="<?= htmlspecialchars($footerData["ins_gra"]) ?>">
        <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" alt="Instagram">
    </a>
</div>
        </div>  

    </div>
    <div class="uta-footer-bottom">
                 <?= htmlspecialchars($footerData["derechos"]) ?>
                                  <br>
                <a href="https://sdsnt2003.atlassian.net/servicedesk/customer/portal/102" target="_blank"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>Encontraste un fallo?</a>
    </div>
</footer>


</body>
</html>
