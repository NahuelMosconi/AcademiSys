<?php
// ============================================================
//  Pantalla de Docentes (solo Administrador). Alta de docentes con DNI y
//  listado con buscador. Da de baja (baja lógica).
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Docente.php';

$docenteModel = new Docente();
$mensaje = ''; $tipo = '';
// ----- Alta de docente (al enviar el formulario) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $dni    = trim($_POST['dni'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    if ($nombre===''||$dni===''||$email==='') { $mensaje='Completá todos los campos.'; $tipo='error'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $mensaje='Email inválido.'; $tipo='error'; }
    else {
        try { $docenteModel->crear($nombre, $dni, $email); $mensaje='Docente agregado.'; $tipo='exito'; }
        catch (PDOException $e) {
            $m = $e->getMessage();
            if (strpos($m,'dni')!==false)       $mensaje='Ese DNI ya está registrado.';
            elseif (strpos($m,'email')!==false) $mensaje='Ese email ya está registrado.';
            else $mensaje='Ya existe un docente con esos datos.';
            $tipo='error';
        }
    }
}
if (isset($_GET['baja'])) { $docenteModel->darDeBaja((int)$_GET['baja']); $mensaje='Docente dado de baja.'; $tipo='exito'; }

$q = trim($_GET['q'] ?? '');
$docentes = $docenteModel->listar($q);
?>
<div class="encabezado-pagina"><h1>Docentes</h1><p>Gestión del cuerpo docente.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2>Agregar docente</h2>
<form method="POST" class="formulario">
    <label>Nombre completo</label><input type="text" name="nombre" required>
    <label>DNI</label><input type="text" name="dni" required>
    <label>Email</label><input type="email" name="email" required>
    <button type="submit">Agregar</button>
</form>
</div>

<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Buscar por nombre, DNI o email...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="docentes.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>

<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Nombre</th><th>DNI</th><th>Email</th><th>Acción</th></tr></thead>
    <tbody>
        <?php if (empty($docentes)): ?><tr><td colspan="5" style="text-align:center;color:#9ca3af;">Sin resultados.</td></tr><?php endif; ?>
        <?php foreach ($docentes as $d): ?>
            <tr><td><?= (int)$d['id_docente'] ?></td><td><?= htmlspecialchars($d['nombre']) ?></td>
            <td><?= htmlspecialchars($d['dni']) ?></td><td><?= htmlspecialchars($d['email']) ?></td>
            <td><a href="docentes.php?baja=<?= (int)$d['id_docente'] ?>" onclick="return confirm('¿Baja?')">Baja</a></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
