<!DOCTYPE html>
<html lang="en">
<head>
  <title>Reestablecer contraseña | Eko Logistic</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <!-- [Favicon] icon -->
  <link rel="icon" href="./assets/images/ekologistic.png" type="image/x-icon" />
  <!-- [Google Font : Public Sans] -->
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" >
  <link rel="stylesheet" href="../assets/css/style-preset.css" >
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body data-pc-preset="preset-1" data-pc-sidebar-theme="light" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
  <!-- Pre-loader, header, etc. pueden ir aquí -->
  <div class="auth-main v1">
    <div class="auth-wrapper">
      <div class="auth-form">
        <div class="card my-5">
          <div class="card-body">
            <div class="text-center">
              <img src="../assets/images/authentication/img-auth-reset-password.png" alt="images" class="img-fluid mb-3">
              <h4 class="f-w-500 mb-1">Reiniciar Contraseña</h4>
              <p class="mb-3">Volver al <a href="../" class="link-primary ms-1">Inicio de sesion</a></p>
            </div>
            <!-- Formulario de reset de contraseña -->
            <form id="resetPasswordForm">
              <div class="mb-3">
                <label class="form-label">Nueva contraseña</label>
                <input type="password" class="form-control" id="newPassword" placeholder="Nueva contraseña" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirmar Nueva Contraseña" required>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" style="background-color:#afc97c" class="btn text-white">Reiniciar Contraseña</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- Footer, etc. -->
    </div>
  </div>

  <!-- Scripts necesarios -->
  <script>
    // Extraemos el token de la URL (por ejemplo, reset-password.php?token=abcdef)
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    if (!token) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Token no encontrado en la URL.'
      });
      // Opcional: ocultar el formulario para que no se intente enviar nada.
      document.getElementById('resetPasswordForm').style.display = 'none';
    }

    // Manejo del formulario para resetear la contraseña
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const newPassword = document.getElementById('newPassword').value;
      const confirmPassword = document.getElementById('confirmPassword').value;

      if (newPassword !== confirmPassword) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Las contraseñas no coinciden.'
        });
        return;
      }

      // Mostrar SweetAlert de carga
      Swal.fire({
        title: 'Actualizando contraseña...',
        text: 'Espere por favor',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Enviar la nueva contraseña y el token al script PHP vía POST (JSON)
      fetch('reset_password.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          token: token,
          newPassword: newPassword
        })
      })
      .then(response => response.json())
      .then(data => {
        Swal.close(); // Cerrar el mensaje de carga
        if (data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: data.message
          }).then(() => {
            // Redireccionar al dashboard en caso de éxito
            window.location.href = '../index.php';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message
          });
        }
      })
      .catch(error => {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Ocurrió un error al actualizar la contraseña.'
        });
        console.error('Error:', error);
      });
    });
  </script>
  <!-- Otros scripts de la plantilla -->
  <script src="../assets/js/plugins/popper.min.js"></script>
  <script src="../assets/js/plugins/simplebar.min.js"></script>
  <script src="../assets/js/plugins/bootstrap.min.js"></script>
  <script src="../assets/js/pcoded.js"></script>
  <script src="../assets/js/plugins/feather.min.js"></script>
</body>
</html>
