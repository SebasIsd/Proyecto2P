
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Formulario - Universidad Técnica de Ambato</title>
<link rel="stylesheet" href="../../css/estiloRegistrarse.css">
</head>
<body>
<main class="container">
<header class="header">
<h1>Registro - Universidad Técnica de Ambato</h1>
<p class="sub">Por favor completa todos los campos. Los campos con * son obligatorios.</p>
</header>


<form id="formRegistro" class="form" action="#" method="post" autocomplete="on">
<fieldset>
<legend>Datos personales</legend>

<div class="row">
  <div class="field">
    <label for="cedula">Cédula *</label>
    <input id="cedula" name="cedula" type="text" maxlength="10"
           placeholder="ej :1850098433" required
           oninput="soloNumeros(this)" onblur="validarCedula(this)">
    <small id="msgCedula">Ingrese 10 dígitos sin guiones.Sin cedula no se habilitan mas campos</small>
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
  
    <input id="segundo_nombre" name="segundo_nombre" type="text" placeholder="Segundo nombre">
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
<input id="segundo_apellido" name="segundo_apellido" type="text" maxlength="50" placeholder="Segundo Apellido">
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

<fieldset>
<legend>Acceso y contacto</legend>


<div class="row">
<div class="field">
<label for="correo">Correo electrónico *</label>
<input id="correo" name="correo" type="email" maxlength="100" placeholder="nombre@uta.edu.ec" required>
</div>


<div class="field">
<label for="password">Contraseña *</label>
<input id="password" name="password" type="password" minlength="8" maxlength="64" placeholder="Mínimo 8 caracteres" required>
<small>Usa al menos 8 caracteres.</small>
</div>
</div>
<div class="field">
<label for="telefono">Teléfono *</label>
<input id="telefono" name="telefono" type="tel" inputmode="tel" pattern="[0-9]{7,10}" maxlength="10" placeholder="0991234567" required>
</div>
</div>

<div class="row">
<div class="field full">
<label for="carrera">Carrera *</label>
<select id="carrera" name="carrera" required>
<option value="">-- Seleccione Carrera --</option>
<option value="ingenieria_sistemas">Ingeniería en Sistemas</option>
<option value="ingenieria_electronica">Ingeniería Electrónica</option>
<option value="ingenieria_industrial">Ingeniería Industrial</option>
<option value="administracion_empresas">Administración de Empresas</option>
<option value="otra">Otra</option>
</select>
</div>
</div>


</fieldset>


<div class="actions">
<button type="submit" class="btn primary">Registrar</button>
<button type="reset" class="btn">Limpiar</button>
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
    //validar cedula
  // Función: solo permitir números mientras se escribe
  function soloNumeros(input) {
    input.value = input.value.replace(/[^0-9]/g, ''); // elimina todo lo que no sea número
  }

  // Función: validar la cédula ecuatoriana
  function validarCedula(input) {
    const cedula = input.value.trim();
    const msg = document.getElementById('msgCedula');

    // Verifica longitud exacta
    if (cedula.length !== 10) {
      msg.style.color = "red";
      msg.textContent = "⚠️ La cédula debe tener exactamente 10 dígitos.";
      input.focus();
      return false;
    }

    // Verifica que los dos primeros dígitos sean una provincia válida (01–24)
    const provincia = parseInt(cedula.substring(0, 2), 10);
    if (provincia < 1 || provincia > 24) {
      msg.style.color = "red";
      msg.textContent = "⚠️ Código de provincia no válido (01–24).";
      input.focus();
      return false;
    }

    // Algoritmo de validación (módulo 10)
    const digitos = cedula.split('').map(Number);
    const verificador = digitos.pop();
    let suma = 0;

    for (let i = 0; i < digitos.length; i++) {
      let valor = digitos[i];
      if (i % 2 === 0) { // posiciones impares (0-index)
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
      input.focus();
      return false;
    }
  }

  // Configurar eventos cuando cargue la página
  document.addEventListener("DOMContentLoaded", () => {
    const cedulaInput = document.getElementById("cedula");
    // Poner el foco automáticamente en el campo de cédula
    cedulaInput.focus();

    // Validaciones dinámicas
    cedulaInput.addEventListener("input", () => soloNumeros(cedulaInput));
    cedulaInput.addEventListener("blur", () => {
      const valido = validarCedula(cedulaInput);
      if (!valido) {
        setTimeout(() => cedulaInput.focus(), 0); // Reenfoca si no pasa la validación
      }
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
// Esperar que el documento cargue
document.addEventListener("DOMContentLoaded", () => {
  // Obtener campos de nombres y apellidos
  const primerNombre = document.getElementById("primer_nombre");
  const segundoNombre = document.getElementById("segundo_nombre");
  const primerApellido = document.getElementById("primer_apellido");
  const segundoApellido = document.getElementById("segundo_apellido");
  
  // Campo de correo y su mensaje
  const correo = document.getElementById("correo");
  const correoField = correo.closest(".field");
  const msgCorreo = document.createElement("small");
  msgCorreo.id = "msgCorreo";
  msgCorreo.style.color = "red";
  msgCorreo.textContent = "⚠️ Completa tus nombres y apellidos antes de ingresar el correo.";
  correoField.appendChild(msgCorreo);

  // Bloquear correo al inicio
  correo.disabled = true;

  // Función para verificar si los campos están llenos
  function verificarCampos() {
    const nombresLlenos = primerNombre.value.trim() !== "" && primerApellido.value.trim() !== "";
    if (nombresLlenos) {
      correo.disabled = false;
      msgCorreo.style.color = "green";
      msgCorreo.textContent = "✔ Ya puedes ingresar tu correo.";
    } else {
      correo.disabled = true;
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "⚠️ Completa tus nombres y apellidos antes de ingresar el correo.";
      correo.value = "";
    }
  }

  // Escuchar cambios en los campos de nombre y apellido
  primerNombre.addEventListener("input", verificarCampos);
  segundoNombre.addEventListener("input", verificarCampos);
  primerApellido.addEventListener("input", verificarCampos);
  segundoApellido.addEventListener("input", verificarCampos);
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const primerNombre = document.getElementById("primer_nombre");
  const primerApellido = document.getElementById("primer_apellido");
  const cedula = document.getElementById("cedula");
  const correo = document.getElementById("correo");

  // Crear mensaje bajo el correo
  const correoField = correo.closest(".field");
  const msgCorreo = document.createElement("small");
  msgCorreo.id = "msgCorreo";
  msgCorreo.style.color = "red";
  msgCorreo.textContent = "⚠️ Completa tus datos antes de generar el correo institucional.";
  correoField.appendChild(msgCorreo);

  // Bloquear correo al inicio
  correo.disabled = true;

  // Función para habilitar el correo si hay datos suficientes
  function habilitarCorreo() {
    const tieneDatos =
      primerNombre.value.trim() !== "" &&
      primerApellido.value.trim() !== "" &&
      /^\d{10}$/.test(cedula.value.trim());

    if (tieneDatos) {
      correo.disabled = false;
      generarCorreo();
    } else {
      correo.disabled = true;
      correo.value = "";
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "⚠️ Completa nombre, apellido y cédula válidos para generar el correo.";
    }
  }

  // Función para generar automáticamente el correo institucional
  function generarCorreo() {
    const nombre = primerNombre.value.trim().toLowerCase();
    const apellido = primerApellido.value.trim().toLowerCase();
    const ced = cedula.value.trim();

    if (nombre && apellido && ced.length === 10) {
      const inicial = nombre.charAt(0);
      const ultimos4 = ced.slice(-4);
      const correoGenerado = `${inicial}${apellido}${ultimos4}@uta.edu.ec`;

      correo.value = correoGenerado;
      msgCorreo.style.color = "green";
      msgCorreo.textContent = `✔ Correo institucional generado correctamente: ${correoGenerado}`;
    }
  }

  // Validar si el usuario intenta cambiar el correo
  correo.addEventListener("input", () => {
    const valor = correo.value.trim();
    const regex = /^[a-z]\w+@uta\.edu\.ec$/i;

    if (!regex.test(valor)) {
      msgCorreo.style.color = "red";
      msgCorreo.textContent = "❌ El correo debe tener el formato institucional (ej: jlopez1234@uta.edu.ec)";
    } else {
      msgCorreo.style.color = "green";
      msgCorreo.textContent = "✔ Correo válido de la UTA.";
    }
  });

  // Escuchar cambios en los campos clave
  primerNombre.addEventListener("input", habilitarCorreo);
  primerApellido.addEventListener("input", habilitarCorreo);
  cedula.addEventListener("input", habilitarCorreo);
});
</script>






</body>
</html>