
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
  
    <input id="segundo_nombre" name="segundo_nombre" type="text" placeholder="Segundo nombre">
  </div>
</div>
<div class="row">
<div class="field">
  <span class="section-title">Apellidos</span>
  <br>
<br>
<input id="primer_apellido" name="primer_apellido" type="text" maxlength="50" placeholder="Pérez" required>
</div>


<div class="field">
<label for="segundo_apellido">Segundo Apellido</label>
<input id="segundo_apellido" name="segundo_apellido" type="text" maxlength="50" placeholder="Gómez">
</div>
</div>


<div class="row">
<div class="field">
<label for="fecha_nac">Fecha de Nacimiento *</label>
<input id="fecha_nac" name="fecha_nac" type="date" required>
</div>

<div class="field">
<label for="rol">Rol *</label>
<select id="rol" name="rol" required>
<option value="">-- Seleccione --</option>
<option value="estudiante">Estudiante</option>
<option value="docente">Docente</option>
<option value="administrativo">Administrativo</option>
<option value="otro">Otro</option>
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




</body>
</html>