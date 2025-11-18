<?php
// Usar __DIR__ para rutas absolutas seguras
// Incluir la consulta de autoridades y otras configuraciones
require __DIR__ . '/home/autoridades.php';
$sliderData = include __DIR__ . "/home/slider.php";
$misionVision = include __DIR__ . '/home/mision_vision.php';
$footerData = include __DIR__ . '/home/footer.php';

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
<html>

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <link rel="shortcut icon" href="images/favico.png" type="">

  <title> Eventos - UTA </title>

  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css" integrity="sha512-CruCP+TD3yXzlvvijET8wV5WxxEh5H8P4cmz0RFbKK6FlZ2sYl3AEsKlLPHbniXKSrDdFewhbmBK5skbdsASbQ==" crossorigin="anonymous" />
  <link href="css/font-awesome.min.css" rel="stylesheet" />
  <link href="css/style.css" rel="stylesheet" />
  <link href="css/responsive.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/estiloslogin.css">
</head>

<body>

  <div class="hero_area">
    <div class="bg-box">
      <img src="images/hero-bg.jpg" alt="">
    </div>
    <header class="header_section">
      <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
          <a class="navbar-brand" href="index.php">
            <span>
              Eventos CTT UTA
            </span>
          </a>

          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class=""> </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav  mx-auto ">
              <li class="nav-item active">
                <a class="nav-link" href="index.php">Inicio <span class="sr-only"></span></a>
              </li>
              <li class="nav-item">
              </li>
              <li class="nav-item">
                <a class="nav-link" href="contacto.php">Contactanos</a>
              </li>
            </ul>
            <div class="user_option">
              <a href="#" class="user_link"></a>
              <a class="cart_link" href="#">
              </a>
              <form class="form-inline">
                <button class="btn  my-2 my-sm-0 nav_search-btn" type="submit"></button>
              </form>
              <a href="#" class="order_online" data-bs-toggle="modal" data-bs-target="#loginModal">login</a>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <section class="slider_section ">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
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

  <section class="offer_section layout_padding-bottom py-5">
    <h1 class="text-center mb-4" style="font-size:34px; color:#6d1313;">AUTORIDADES</h1>
    <div class="container">
      <div class="row g-4">

        <?php if (!empty($autoridades)): ?>
          <?php foreach ($autoridades as $a):
            $id = (int)$a['id'];
            $modalId = "autoridadModal_$id";
            $foto = htmlspecialchars($a['foto'] ?? 'images/placeholder.jpg');
            $titulo = htmlspecialchars(trim($a['titulo'] ?? ''));
            $nombre = htmlspecialchars(trim($a['nombre'] ?? ''));
            $cargo = htmlspecialchars(trim($a['cargo'] ?? 'Autoridad'));
            $encabezado = trim(($titulo ? $titulo . ' ' : '') . $nombre);
            $resumen = htmlspecialchars($a['resumen'] ?? '');
            $direccion = htmlspecialchars($a['direccion'] ?? '');
            $telefono = htmlspecialchars($a['telefono'] ?? '');
            $ext = htmlspecialchars($a['telefono_ext'] ?? '');
            $horario = htmlspecialchars($a['horario'] ?? '');
            $email = htmlspecialchars($a['email'] ?? '');
            ?>
            <div class="col-12">
              <div class="card shadow-sm border-0">
                <div class="row g-0">
                  <div class="col-md-4 bg-light text-center p-3">
                    <img src="<?= $foto ?>" alt="<?= $encabezado ?>" class="img-fluid rounded">
                    <?php if ($email): ?>
                      <div class="mt-2">
                        <a href="mailto:<?= $email ?>" style="color:#6d1313; font-weight:600;">
                          <?= $email ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="col-md-8 d-flex flex-column justify-content-center p-4">
                    <div style="font-size:1rem; color:#6d1313; font-weight:700;"><?= $cargo ?></div>
                    <h5 class="fw-bold"><?= $encabezado ?></h5>
                    <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">Más detalles</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title"><?= $encabezado ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <?php if ($resumen): ?><p><?= nl2br($resumen) ?></p><?php endif; ?>
                    <ul>
                      <?php if ($direccion): ?><li><strong>Dirección:</strong> <?= $direccion ?></li><?php endif; ?>
                      <?php if ($telefono): ?><li><strong>Teléfono:</strong> <?= $telefono ?><?= $ext ? ' ext ' . $ext : '' ?></li><?php endif; ?>
                      <?php if ($horario): ?><li><strong>Horario:</strong> <?= nl2br($horario) ?></li><?php endif; ?>
                    </ul>
                  </div>
                  <div class="modal-footer">
                    <?php if ($email): ?><a href="mailto:<?= $email ?>" class="btn btn-primary">Contactar</a><?php endif; ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center">No hay autoridades registradas.</p>
        <?php endif; ?>

      </div>
    </div>
  </section>
  <section class="about_section layout_padding" style="background-color: #6f0909;">
    <div class="container">
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="box">
            <h3><strong>Misión</strong></h3>
            <p><?= nl2br(htmlspecialchars($misionVision["mision"], ENT_QUOTES, 'UTF-8')) ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box">
            <h3><strong>Visión</strong></h3>
            <p><?= nl2br(htmlspecialchars($misionVision["vision"], ENT_QUOTES, 'UTF-8')) ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="food_section layout_padding-bottom">
    <div class="container">
      <div class="heading_container heading_center">
        <h2 style="color: #6f0909;">Eventos</h2>
      </div>

      <ul class="filters_menu" id="filtrosCarreras">
        </ul>

      <div class="filters-content">
        <div class="row" id="contenedorEventos">
          </div>
      </div>
    </div>
  </section>
  <section class="comment_section layout_padding">
    <div class="container" id="comentarios">
      <div class="heading_container">
        <h2>Comentarios recientes</h2>
      </div>

      <div id="listaComentarios">
        <p>Cargando comentarios...</p>
      </div>

      <button class="btn btn-warning" onclick="location.href='contacto.php?from=add'" style="background-color: #6f0909; color: white;">
        Añadir comentario
      </button>

    </div>
  </section>
  <footer class="footer_section" style="background-color: #6f0909;">
    <div class="container">
      <div class="row">
        <div class="col-md-4 footer-col">
          <div class="footer_contact">
            <h4>Contacto</h4>
            <div class="contact_link_box">
              <a>
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span><?= htmlspecialchars($footerData["telefono"]) ?></span>
              </a>
              <a>
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span><?= htmlspecialchars($footerData["correo"]) ?></span>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <div class="footer_detail">
            <a class="footer-logo">Uta</a>
            <p><?= htmlspecialchars($footerData["Des_logo"]) ?></p>
            <div class="footer_social">
              <a href="<?= htmlspecialchars($footerData["face"]) ?>"><i class="fa fa-facebook" aria-hidden="true"></i></a>
              <a href="<?= htmlspecialchars($footerData["ins_gra"]) ?>"><i class="fa fa-instagram" aria-hidden="true"></i></a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <h4>Horarios De atencion</h4>
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
  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="js/bootstrap.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
  <script src="js/custom.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // 1. Carga de filtros de carreras
      fetch("./login/registrar/car.php")
        .then(res => res.json())
        .then(data => {
          let contenedor = document.getElementById("filtrosCarreras");
          if (!contenedor) return;

          let btnTodos = document.createElement("li");
          btnTodos.textContent = "Todos";
          btnTodos.dataset.filter = ".all";
          btnTodos.classList.add("active");
          contenedor.appendChild(btnTodos);

          let btnSin = document.createElement("li");
          btnSin.textContent = "Sin carrera";
          btnSin.dataset.filter = data.sin_carrera;
          contenedor.appendChild(btnSin);

          data.carreras.forEach(c => {
            let li = document.createElement("li");
            li.dataset.filter = `.carrera_${c.id}`;
            li.textContent = c.nombre;
            contenedor.appendChild(li);
          });

          contenedor.querySelectorAll("li").forEach(li => {
            li.addEventListener("click", function() {
              contenedor.querySelectorAll("li").forEach(item => item.classList.remove("active"));
              this.classList.add("active");
              filtrarEventos(this.dataset.filter);
            });
          });
        })
        .catch(err => console.error("Error al cargar filtros de carrera:", err));

      // 2. Carga y visualización de eventos
      fetch("./login/registrar/eventos.php")
        .then(res => res.json())
        .then(data => {
          mostrarEventos(data);
        })
        .catch(err => console.error("Error al cargar eventos:", err));

      // 3. Carga de comentarios
      fetch("./home/obtener_comentarios.php")
        .then(r => r.json())
        .then(comments => {
          const cont = document.getElementById("listaComentarios");
          if (!cont) return;

          if (comments.length === 0) {
            cont.innerHTML = "<p>No hay comentarios aún.</p>";
            return;
          }

          let html = "";
          comments.forEach(c => {
            // Se utiliza htmlspecialchars() para sanitizar la salida
            const nombre = c.nombre ? htmlspecialchars(c.nombre) : 'Anónimo';
            const fecha = c.fecha ? htmlspecialchars(c.fecha) : '';
            const comentario = c.comentario ? htmlspecialchars(c.comentario) : '';

            html += `
              <div class="comment_item" 
                   style="border:1px solid #eee; padding:15px; border-radius:10px; margin-bottom:15px;">
                <p><strong>${nombre}</strong> <span style="color:#999;">(${fecha})</span></p>
                <p>${comentario}</p>
              </div>
            `;
          });
          cont.innerHTML = html;
        })
        .catch(err => console.error("Error al cargar comentarios:", err));
    });

    function mostrarEventos(data) {
      let contenedor = document.getElementById("contenedorEventos");
      contenedor.innerHTML = "";

      data.forEach(evento => {
        const id_carrera = evento.id_carrera ? evento.id_carrera : null;
        const claseCarrera = id_carrera ? `carrera_${id_carrera}` : "sin_carrera";

        // Sanitización para XSS antes de insertar en el DOM
        const nombre = htmlspecialchars(evento.nombre || '');
        const descripcion = htmlspecialchars(evento.descripcion || '');
        const imagen = htmlspecialchars(evento.imagen || '');
        const fecha = htmlspecialchars(evento.fecha || '');

        let div = document.createElement("div");
        div.className = `col-sm-6 col-lg-4 all ${claseCarrera}`;

        div.innerHTML = `
            <div class="box" >
                <div>
                    <div class="img-box">
                        <img src="${imagen}" alt="${nombre}">
                    </div>
                    <div class="detail-box" style="background-color: #6f0909;">
                        <h5>${nombre}</h5>
                        <p>${descripcion}</p>
                        <div class="options">
                        <h6>Fecha de duracion</h6>
                            <h6>${fecha}</h6>
                        </div>
                        <button  class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal" 
                        data-redirect="buscar_eventos.php" >
                        Inscribirse
                    </button>
                </div>
                    </div>
                </div>
            </div>
        `;
        contenedor.appendChild(div);
      });
    }

    function filtrarEventos(filtro) {
      let eventos = document.querySelectorAll("#contenedorEventos .all");

      eventos.forEach(e => {
        // Elimina el punto para comparar clases
        const filtroClass = filtro.startsWith('.') ? filtro.substring(1) : filtro;

        if (filtro === ".all") {
          e.style.display = "block";
        } else if (e.classList.contains(filtroClass)) {
          e.style.display = "block";
        } else {
          e.style.display = "none";
        }
      });
    }
    
    // Función simple de sanitización de HTML para JS
    function htmlspecialchars(str) {
      if (typeof str !== 'string') return '';
      return str.replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
    }
  </script>
  <?php // La lógica de los modales se mantiene abajo, fuera del body para seguir la estructura original ?>
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
</body>
</html>