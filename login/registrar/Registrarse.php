
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Formulario - Universidad Técnica de Ambato</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../css/estiloRegistrarse.css">
</head>
<body>
<main class="container">
<header class="header">
<h1>Registro - Universidad Técnica de Ambato</h1>
</header>



<form id="formRegistro" class="form" action="registrar_usuario.php" method="post" autocomplete="on">
<fieldset>
<legend>Datos personales</legend>

<div class="row">
  <div class="field">
    <label for="cedula">Cédula *</label>
    <input id="cedula" name="cedula" type="text" maxlength="10"
           placeholder="ej :1850098433" required
           oninput="soloNumeros(this)" onblur="validarCedula(this)">
    <small id="msgCedula">Ingrese 10 dígitos sin guiones.</small>
  </div>
</div>

<span class="section-title">Nombres</span>
<br>
<br>
<div class="row">
  <div class="field">
    
    <input id="primer_nombre" name="primer_nombre" type="text" required placeholder="Primer nombre">
  </div>
  <div class="field">
  
    <input id="segundo_nombre" name="segundo_nombre" type="text" placeholder="Segundo nombre" required>
  </div>
</div>
  <span class="section-title">Apellidos</span>
   <br>
<br>
<div class="row">
<div class="field">
 
<input id="primer_apellido" name="primer_apellido" type="text" maxlength="50" placeholder="Primer Apellido" required>
</div>


<div class="field">
<input id="segundo_apellido" name="segundo_apellido" type="text" maxlength="50" placeholder="Segundo Apellido" required>
</div>
</div>


<div class="row">
<div class="field">
<label for="fecha_nac">Fecha de Nacimiento</label>
<input id="fecha_nac" name="fecha_nac" type="date" required max="">

</div>

<div class="field">
  <label for="rol">Rol</label>
  <select id="rol" name="rol" required>
</select>
</div>
 </div>

</fieldset>
<div class="row">
  <div class="field">
    <label for="telefono">Teléfono *</label>
    <input id="telefono" name="telefono" type="tel" inputmode="tel"
           pattern="[0-9]{7,10}" maxlength="10"
           placeholder="0991234567" required>
  </div>

  <div class="field">
    <label for="direccion">Dirección *</label>
    <input id="direccion" name="direccion" type="text" maxlength="50"
           placeholder="Ej: Av. Los Andes y Av. Quito" required>
  </div>
</div>


<fieldset>
<legend>Acceso y contacto</legend>


<div class="row">
<div class="field">
<label for="correo">Correo electrónico *</label>
<input id="correo" name="correo" type="email" maxlength="100" placeholder="nombre@uta.edu.ec" required>
</div>






<div class="field" style="position: relative;">
  <label for="password">Contraseña *</label>
  <input id="password" name="password" type="password" minlength="8" maxlength="64" placeholder="Mínimo 8 caracteres" required
         style="padding-right: 40px;">
  <span id="togglePassword" 
        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #555;">
    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 
               5.5 8 5.5S16 8 16 8zM1.173 
               8a13.133 13.133 0 0 1 1.66-2.043C4.12 
               4.668 5.88 3.5 8 3.5c2.12 0 
               3.879 1.168 5.168 2.457A13.133 
               13.133 0 0 1 14.828 8a13.133 
               13.133 0 0 1-1.66 2.043C11.879 
               11.332 10.12 12.5 8 12.5c-2.12 
               0-3.879-1.168-5.168-2.457A13.133 
               13.133 0 0 1 1.172 8z"/>
      <path d="M8 5.5a2.5 2.5 0 1 0 
               0 5 2.5 2.5 0 0 0 0-5zM8 
               4a4 4 0 1 1 0 8A4 4 0 0 1 8 4z"/>
    </svg>
  </span>
</div>









</div>

</div>

<div class="row">
<div class="field full">
<label for="carrera">Carrera *</label>
<select id="carrera" name="carrera" required>
  <option value="">-- Seleccione Carrera --</option>
</select>

</div>
</div>


</fieldset>


<div class="actions">
<button type="submit" class="btn primary">Registrar</button>
<button type="button" id="btnVolver" class="btn" onclick="window.location.href='../../index.php'">Volver al inicio</button>
</div>


<p class="note">Al enviar aceptas la política de manejo de datos de la Universidad Técnica de Ambato.</p>

</form>


<footer class="footer">
<small>Universidad Técnica de Ambato • Facultad de Ingeniería</small>
</footer>
</main>


<script>
// Pequeña validación para mostrar mensajes más amigables
document.getElementById('formRegistro').addEventListener('submit', function(e){
const ced = document.getElementById('cedula');
if(!/^\d{10}$/.test(ced.value)){
e.preventDefault();
alert('La cédula debe contener exactamente 10 dígitos.');
ced.focus();
return false;
}


// aquí podrías agregar validaciones adicionales o envío por fetch
//validar cedula



});
</script>



<script>
  // Función: solo permitir números mientras se escribe
  function soloNumeros(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
  }

  function validarCedulaInput(input) {
    const cedula = input.value.trim();
    const msg = document.getElementById('msgCedula');

    if (cedula.length === 0) {
      msg.style.color = "gray";
      msg.textContent = "Ingrese su cédula (10 dígitos)";
      return false;
    } else if (cedula.length < 10) {
      msg.style.color = "orange";
      msg.textContent = `Escribiendo... (${cedula.length}/10 dígitos)`;
      return false;
    }

    if (cedula.length === 10) {
      return validarCedula(cedula);
    }
  }

  function validarCedula(cedula) {
    const msg = document.getElementById('msgCedula');
    if (cedula.length !== 10) {
      msg.style.color = "red";
      msg.textContent = "⚠️ La cédula debe tener exactamente 10 dígitos";
      return false;
    }

    const provincia = parseInt(cedula.substring(0, 2), 10);
    if (provincia < 1 || provincia > 24) {
      msg.style.color = "red";
      msg.textContent = "⚠️ Código de provincia no válido (01–24).";
      return false;
    }

    const digitos = cedula.split('').map(Number);
    const verificador = digitos.pop();
    let suma = 0;

    for (let i = 0; i < digitos.length; i++) {
      let valor = digitos[i];
      if (i % 2 === 0) {
        valor *= 2;
        if (valor > 9) valor -= 9;
      }
      suma += valor;
    }

    const decenaSuperior = Math.ceil(suma / 10) * 10;
    const digitoValido = (decenaSuperior - suma) % 10;

    if (digitoValido === verificador) {
      msg.style.color = "green";
      msg.textContent = "✔ Cédula válida.";
      return true;
    } else {
      msg.style.color = "red";
      msg.textContent = "❌ Cédula no válida.";
      return false;
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    const cedulaInput = document.getElementById("cedula");
    cedulaInput.focus();

    cedulaInput.addEventListener("input", () => {
      soloNumeros(cedulaInput);
      validarCedulaInput(cedulaInput);
    });

    cedulaInput.addEventListener("blur", () => {
      validarCedula(cedulaInput.value);
    });

    // Botón "Volver al inicio" ignora validación siempre
    const btnVolver = document.getElementById("btnVolver");
    btnVolver.addEventListener("click", (e) => {
      // Redirige sin activar ninguna validación
      window.location.href = btnVolver.getAttribute("data-href") || '../../index.php';
    });
  });
</script>









<script>
  //fecha de nacimiento no mayor a hoy
document.addEventListener("DOMContentLoaded", function() {
  const hoy = new Date().toISOString().split("T")[0];
  document.getElementById("fecha_nac").setAttribute("max", hoy);
});
</script>
<script>
// Al cargar la página, se ejecuta esta función
document.addEventListener("DOMContentLoaded", () => {
    fetch('roles.php')
        .then(response => response.json())
        .then(data => {
            const selectRol = document.getElementById('rol');

            data.forEach(rol => {
                // ❌ Ignorar si el rol es "Administrador"
                if (rol.NOM_ROL.toLowerCase() === 'administrador') return;

                const option = document.createElement('option');
                option.value = rol.ID_ROL;
                option.textContent = rol.NOM_ROL;
                selectRol.appendChild(option);
            });
        })
        .catch(error => console.error('Error al cargar los roles:', error));
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const primerNombre = document.getElementById("primer_nombre");
  const segundoNombre = document.getElementById("segundo_nombre");
  const primerApellido = document.getElementById("primer_apellido");
  const segundoApellido = document.getElementById("segundo_apellido");
  const cedula = document.getElementById("cedula");
  const correo = document.getElementById("correo");

  // Crear mensaje informativo debajo del campo de correo
  const correoField = correo.closest(".field");
  const msgCorreo = document.createElement("small");
  msgCorreo.id = "msgCorreo";
  msgCorreo.style.display = "block";
  msgCorreo.style.marginTop = "4px";
  msgCorreo.style.color = "red";
  msgCorreo.textContent = "⚠️ Completa tus datos antes habilitar el correo.";
  correoField.appendChild(msgCorreo);

  // Desactivar el campo correo al inicio
  correo.disabled = true;

  // Verificar si los campos obligatorios están llenos
  function verificarDatos() {
    const nombreValido = primerNombre.value.trim() !== "";
    const apellidoValido = primerApellido.value.trim() !== "";
    const cedulaValida = /^\d{10}$/.test(cedula.value.trim());
    return nombreValido && apellidoValido && cedulaValida;
  }

  // Generar correo institucional automáticamente
  function generarCorreo() {
    const nombre = primerNombre.value.trim().toLowerCase();
    const apellido = primerApellido.value.trim().toLowerCase();
    const ced = cedula.value.trim();
    const inicial = nombre.charAt(0);
    const ultimos4 = ced.slice(-4);
    return `${inicial}${apellido}${ultimos4}@uta.edu.ec`;
  }

  // Función principal que controla la lógica
  function actualizarEstadoCorreo() {
    if (verificarDatos()) {
      correo.disabled = false;
      correo.value = generarCorreo();
      msgCorreo.style.color = "green";
      msgCorreo.textContent = "✔ Correo Valido";
    } else {
      correo.disabled = true;
      correo.value = "";
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "⚠️ Completa nombre, apellido y una cédula válida  el correo deberia coincidir con los mismos.";
    }
  }

  // Verificar si el usuario edita el correo manualmente
  correo.addEventListener("input", () => {
    const valor = correo.value.trim().toLowerCase();
    const esperado = generarCorreo();
    if (valor === esperado) {
      msgCorreo.style.color = "green";
      msgCorreo.textContent = "✔ Correo institucional válido.";
    } else if (valor.endsWith("@uta.edu.ec")) {
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "⚠️ El formato del correo no coincide con el institucional.";
    } else {
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "❌ El correo no pertenece al dominio institucional.";
    }
  });

  // Escuchar cambios en los campos relacionados
  [primerNombre, segundoNombre, primerApellido, segundoApellido, cedula].forEach(campo => {
    campo.addEventListener("input", actualizarEstadoCorreo);
  });
});
</script>




<script>
// Validar teléfono ecuatoriano (solo números, 10 dígitos, empieza con 09)
document.addEventListener("DOMContentLoaded", () => {
  const telefono = document.getElementById("telefono");

  // Crear mensaje debajo del campo
  const msgTelefono = document.createElement("small");
  msgTelefono.id = "msgTelefono";
  msgTelefono.style.color = "gray";
  msgTelefono.textContent = "Ingrese un número de celular ecuatoriano válido (09XXXXXXXX).";
  telefono.insertAdjacentElement("afterend", msgTelefono);

  // Permitir solo números y validar estructura
  telefono.addEventListener("input", () => {
    // Solo permitir números
    telefono.value = telefono.value.replace(/[^0-9]/g, '');

    // Limitar a máximo 10 caracteres
    if (telefono.value.length > 10) {
      telefono.value = telefono.value.slice(0, 10);
    }

    // Validar los dos primeros dígitos
    if (telefono.value.length >= 1 && telefono.value[0] !== '0') {
      msgTelefono.style.color = "red";
      msgTelefono.textContent = "❌ El número debe empezar con 09.";
      telefono.value = ""; // Limpia si no empieza con 0
      return;
    }

    if (telefono.value.length >= 2 && telefono.value.substring(0, 2) !== "09") {
      msgTelefono.style.color = "red";
      msgTelefono.textContent = "❌ El número debe empezar con 09.";
      telefono.value = telefono.value.substring(0, 1); // Mantiene solo el '0'
      return;
    }

    // Si está escribiendo correctamente
    if (telefono.value.length > 0 && telefono.value.length < 10) {
      msgTelefono.style.color = "orange";
      msgTelefono.textContent = "Escribiendo... (Debe tener 10 dígitos en total)";
    }

    // Validar completo
    if (telefono.value.length === 10) {
      validarTelefono(telefono);
    }
  });

  // Validar al salir del campo
  telefono.addEventListener("blur", () => validarTelefono(telefono));

  // Función principal de validación
  function validarTelefono(input) {
    const valor = input.value.trim();
    
    if (!/^09\d{8}$/.test(valor)) {
      msgTelefono.style.color = "red";
      msgTelefono.textContent = "❌ Número telefónico no válido. Debe empezar con 09 y tener 10 dígitos.";
      return false;
    } else {
      msgTelefono.style.color = "green";
      msgTelefono.textContent = "✔ Número telefónico válido.";
      return true;
    }
  }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const password = document.getElementById("password");

  // Crear mensaje debajo del campo
  const msgPass = document.createElement("small");
  msgPass.id = "msgPassword";
  msgPass.style.color = "gray";
  msgPass.textContent = "Debe tener al menos 8 caracteres, con mayúsculas, minúsculas, números y uno de estos símbolos: !@$#-_";
  password.insertAdjacentElement("afterend", msgPass);

  // Validar en tiempo real
  password.addEventListener("input", () => {
    validarPassword(password);
  });

  // Función principal de validación
  function validarPassword(input) {
    const valor = input.value;

    // Expresión regular con las reglas requeridas
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@$#\-_])[A-Za-z\d!@$#\-_]{8,}$/;

    // Verificar reglas una por una para dar mensajes detallados
    if (valor.length < 8) {
      msgPass.style.color = "red";
      msgPass.textContent = "❌ La contraseña debe tener al menos 8 caracteres.";
      return false;
    }
    if (!/[a-z]/.test(valor)) {
      msgPass.style.color = "red";
      msgPass.textContent = "❌ Debe incluir al menos una letra minúscula (a-z).";
      return false;
    }
    if (!/[A-Z]/.test(valor)) {
      msgPass.style.color = "red";
      msgPass.textContent = "❌ Debe incluir al menos una letra mayúscula (A-Z).";
      return false;
    }
    if (!/\d/.test(valor)) {
      msgPass.style.color = "red";
      msgPass.textContent = "❌ Debe incluir al menos un número (0-9).";
      return false;
    }
    if (!/[!@$#\-_]/.test(valor)) {
      msgPass.style.color = "red";
      msgPass.textContent = "❌ Debe incluir al menos un símbolo: ! @ $ # - _";
      return false;
    }

    // Si pasa todas las condiciones
    if (regex.test(valor)) {
      msgPass.style.color = "green";
      msgPass.textContent = "✔ Contraseña segura.";
      return true;
    }

    msgPass.style.color = "red";
    msgPass.textContent = "❌ Contraseña no válida.";
    return false;
  }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  fetch('carreras.php') // ruta al archivo PHP que devuelve las carreras
    .then(response => response.json())
    .then(data => {
      const selectCarrera = document.getElementById('carrera');

      data.forEach(carrera => {
        const option = document.createElement('option');
        option.value = carrera.ID_CARRERA; // ID de la carrera (clave foránea)
        option.textContent = carrera.NOMBRE_CARRERA; // nombre visible
        selectCarrera.appendChild(option);
      });
    })
    .catch(error => console.error('Error al cargar las carreras:', error));
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formRegistro");
  const correo = document.getElementById("correo");
  const primerNombre = document.getElementById("primer_nombre");
  const primerApellido = document.getElementById("primer_apellido");
  const cedula = document.getElementById("cedula");
  const msgCorreo = document.getElementById("msgCorreo");

  // Crear contenedor de alerta antes de los botones
  let alerta = document.createElement("div");
  alerta.id = "alerta";
  alerta.className = "mt-3 w-100";
  const botones = form.querySelector(".botones") || form.lastElementChild;
  form.insertBefore(alerta, botones);

  // Función que genera el correo esperado institucional
  function correoEsperado() {
    const nombre = primerNombre.value.trim().toLowerCase();
    const apellido = primerApellido.value.trim().toLowerCase();
    const ced = cedula.value.trim();
    const inicial = nombre.charAt(0);
    const ultimos4 = ced.slice(-4);
    return `${inicial}${apellido}${ultimos4}@uta.edu.ec`;
  }

  // Función para validar el formato del correo institucional
  function validarCorreo() {
    const valor = correo.value.trim().toLowerCase();
    const esperado = correoEsperado();

    if (valor === esperado) {
      msgCorreo.style.color = "green";
      msgCorreo.textContent = "✔ Correo institucional válido.";
      return true;
    } else if (valor.endsWith("@uta.edu.ec")) {
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "⚠️ El formato del correo no coincide con el institucional.";
      return false;
    } else {
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "❌ El correo no pertenece al dominio institucional.";
      return false;
    }
  }

  // Evento submit
  form.addEventListener("submit", function(e) {
    e.preventDefault(); // detener envío por defecto

    // Validar correo antes de enviar
    if (!validarCorreo()) {
      alerta.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show mt-3 text-center" role="alert">
          ⚠️ <strong>Error:</strong> El correo institucional no cumple con el formato requerido.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
      correo.focus();
      return; // no continúa al fetch
    }

    // Si pasa la validación, se envía el formulario normalmente
    const formData = new FormData(this);

    fetch("registrar_usuario.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      alerta.innerHTML = ""; // Limpia alertas anteriores
      const div = document.createElement("div");
      div.classList.add("alert", "alert-dismissible", "fade", "show", "text-center");

      if (data.status === "success") {
        div.classList.add("alert-success");
        div.innerHTML = `
          <strong>✅ Éxito:</strong> ${data.msg}
          <br>La página se recargará en unos segundos...
        `;
        alerta.appendChild(div);
        setTimeout(() => location.reload(), 3000);
      } else {
        div.classList.add("alert-danger");
        div.innerHTML = `<strong>⚠️ Error:</strong> ${data.msg}`;
        alerta.appendChild(div);
      }

      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "btn-close";
      btn.setAttribute("data-bs-dismiss", "alert");
      btn.setAttribute("aria-label", "Close");
      div.appendChild(btn);
    })
    .catch(error => {
      alerta.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show mt-3 text-center" role="alert">
          ❌ <strong>Error:</strong> No se pudo conectar con el servidor.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
      console.error("Error:", error);
    });
  });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("togglePassword");
  const input = document.getElementById("password");
  const icon = document.getElementById("eyeIcon");

  toggle.addEventListener("click", () => {
    const tipo = input.type === "password" ? "text" : "password";
    input.type = tipo;

    // Cambia entre ojo abierto y cerrado
    icon.innerHTML = tipo === "password"
      ? `<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 
               5.5 8 5.5S16 8 16 8zM1.173 
               8a13.133 13.133 0 0 1 1.66-2.043C4.12 
               4.668 5.88 3.5 8 3.5c2.12 0 
               3.879 1.168 5.168 2.457A13.133 
               13.133 0 0 1 14.828 8a13.133 
               13.133 0 0 1-1.66 2.043C11.879 
               11.332 10.12 12.5 8 12.5c-2.12 
               0-3.879-1.168-5.168-2.457A13.133 
               13.133 0 0 1 1.172 8z"/>
         <path d="M8 5.5a2.5 2.5 0 1 0 
               0 5 2.5 2.5 0 0 0 0-5zM8 
               4a4 4 0 1 1 0 8A4 4 0 0 1 8 4z"/>`
      : `<path d="M13.359 11.238l1.357 1.357a.5.5 0 0 1-.708.708l-1.335-1.336C11.708 12.582 9.936 13.5 8 13.5c-5 0-8-5.5-8-5.5a15.007 15.007 0 0 1 2.592-2.855L.646 3.354a.5.5 0 1 1 .708-.708l12 12zM11.297 9.176l-1.424-1.424A2.5 2.5 0 0 0 6.248 8c0 .347.07.678.197.979l-1.41-1.41a4.01 4.01 0 0 1 6.262 1.607z"/>`;
  });
});
</script>







</body>
</html>