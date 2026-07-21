<?php
// ============================================================
//  Formulario de alta/edición de Alumno (solo Administrador).
//  Si llega ?id= edita ese alumno; si no, crea uno nuevo. Incluye la carrera.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Alumno.php';
require_once __DIR__ . '/../clases/Carrera.php';

$alumnoModel = new Alumno();
$error = '';
$id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$datos = ['legajo'=>'', 'nombre' => '', 'dni' => '', 'telefono'=>'', 'email' => '', 'id_carrera'=>''];
if ($id > 0) {
    $e = $alumnoModel->buscar($id);
    if ($e) $datos = $e;
}
// Cargamos las carreras para el combo.
$carreras = (new Carrera())->listar();

// ----- Procesar el formulario (validar y guardar) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legajo = trim($_POST['legajo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $dni    = trim($_POST['dni'] ?? '');
    $tel    = trim($_POST['telefono'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $idCarrera = (int)($_POST['id_carrera'] ?? 0);
    if ($legajo===''||$nombre===''||$dni===''||$tel===''||$email===''||$idCarrera<=0) {
        $error = 'Completá todos los campos, incluida la carrera.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido.';
    } else {
        try {
            if ($id > 0) $alumnoModel->actualizar($id, $legajo, $nombre, $dni, $tel, $email, $idCarrera);
            else         $alumnoModel->crear($legajo, $nombre, $dni, $tel, $email, $idCarrera);
            header('Location: alumnos.php?ok=1'); exit;
        } catch (PDOException $e) {
            // Si un campo UNIQUE choca, detectamos cuál para avisar con claridad.
            $m = $e->getMessage();
            if (strpos($m,'legajo')!==false)      $error = 'Ese legajo ya está registrado.';
            elseif (strpos($m,'dni')!==false)      $error = 'Ese DNI ya está registrado.';
            elseif (strpos($m,'telefono')!==false) $error = 'Ese teléfono ya está registrado.';
            elseif (strpos($m,'email')!==false)    $error = 'Ese email ya está registrado.';
            else $error = 'Ya existe un alumno con esos datos.';
            $datos = ['legajo'=>$legajo,'nombre'=>$nombre,'dni'=>$dni,'telefono'=>$tel,'email'=>$email,'id_carrera'=>$idCarrera];
        }
    }
}
?>
<div class="encabezado-pagina"><h1><?= $id > 0 ? 'Editar' : 'Nuevo' ?> alumno</h1></div>
<a href="alumnos.php" class="boton boton-gris"><?= icono('volver') ?>Volver</a>
<?php if ($error !== ''): ?><p class="aviso error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<div class="tarjeta-form">
<form method="POST" class="formulario">
    <label>Legajo</label>
    <input type="text" name="legajo" value="<?= htmlspecialchars($datos['legajo']) ?>" required>
    <label>Nombre completo</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
    <label>DNI</label>
    <input type="text" name="dni" value="<?= htmlspecialchars($datos['dni']) ?>" required>
    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($datos['telefono']) ?>" required>
    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($datos['email']) ?>" required>
    <label>Carrera</label>
    <select name="id_carrera" required>
        <option value="">-- elegir carrera --</option>
        <?php foreach ($carreras as $c): ?>
            <option value="<?= (int)$c['id_carrera'] ?>" <?= ($datos['id_carrera']==$c['id_carrera'])?'selected':'' ?>>
                <?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Guardar</button>
</form>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
