<?php
session_start();
$error = "";
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GudEngine - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/loguearse.css"> 
</head>
<body class="d-flex align-items-center min-vh-100  bg-primary">
    <div class="container" style="max-width: 1000px;">
        
        <div class="row g-0 rounded shadow overflow-hidden">
            <div class="col-12 col-md-6 p-4 flex-column bg-light" >
                
                <h2 class="fw-bold mb-4">Iniciar Sesión</h2>
                
                <form action="../controlador/login_proceso.php" method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="E-mail" required>
                    </div>
                    
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Contraseña" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-theme btn-lg w-100 mb-3" style=" border: none;">
                        Loguearse
                    </button>
                </form>

                <?php if ($error != ""): ?>
                    <div class="alert alert-danger p-2 small text-center" role="alert">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-2 small">
                    <a href="https://e01-elmundo.uecdn.es/america/imagenes/2010/11/26/deportes/1290762475_0.jpg" target="_blank" class="d-block mb-2 text-decoration-none" style="color: #0d6efd;">
                        ¿Te olvidaste la Contraseña? Mirá una imagen bonita para desestresarte
                    </a>
                    
                    <p class="mb-0">¿No tenés una cuenta? 
                        <a href="index.php" class="fw-bold text-decoration-none" style="color: #0d6efd;">Registrate</a>
                    </p> 
                </div>
                
            </div>

            <div class="col-12 col-md-6 position-relative  align-items-center justify-content-center text-center">
                
                <img src="../img/gudZapato.png" alt="Auto Gud" class="w-100 h-100" style="object-fit: cover; min-height: 300px;">
                
                <div class="position-absolute" style="top: 0%; ">
                    <h2 class="fw-bold text-dark lh-sm" style="text-shadow: 0px 0px 8px rgba(255,255,255,0.8);">
                        El auto de Gud no emite gases
                    </h2>
                </div>
                <div class="position-absolute" style="bottom: 0%; ">
                    <h2 class="fw-bold text-white lh-sm" style="text-shadow: 0px 0px 8px rgba(10, 14, 250, 0.8);">
                        Gud respeta el medio ambiente
                    </h2>
                </div>
                
            </div>

        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>