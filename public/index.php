<?php
require __DIR__ . '/../vendor/autoload.php';

use MiProyecto\Incidentes;

$incidentes = new Incidentes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentes->procesarFormulario($_POST);
}

$incidentes->obtenerReportes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Incidentes Técnicos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h1 class="text-center text-primary mb-4">🔧 Reporte de Incidentes</h1>
  <p class="text-center text-secondary">Sistema de registro técnico de fallos</p>

  <?php if ($incidentes->mensaje): ?>
    <div class="alert alert-<?php echo $incidentes->tipoMensaje; ?> text-center">
      <?php echo $incidentes->mensaje; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="p-4 bg-white shadow rounded mb-4">
    <div class="mb-3"><label class="form-label">Nombre *</label><input name="nombre" required class="form-control"></div>
    <div class="mb-3"><label class="form-label">Email *</label><input name="email" type="email" required class="form-control"></div>
    <div class="mb-3"><label class="form-label">Tipo *</label>
      <select name="tipo_incidente" required class="form-select">
        <option value="">Seleccione</option><option value="hardware">Hardware</option><option value="software">Software</option>
        <option value="red">Red</option><option value="seguridad">Seguridad</option><option value="otro">Otro</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">Prioridad *</label>
      <select name="prioridad" required class="form-select">
        <option value="">Seleccione</option><option value="baja">Baja</option><option value="media">Media</option>
        <option value="alta">Alta</option><option value="critica">Crítica</option>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">Descripción *</label><textarea name="descripcion" required class="form-control"></textarea></div>
    <div class="mb-3"><label class="form-label">Ubicación</label><input name="ubicacion" class="form-control"></div>
    <div class="text-center"><button type="submit" class="btn btn-primary">📤 Enviar Reporte</button></div>
  </form>

  <h2 class="text-primary">📋 Reportes registrados</h2>
  <?php if ($incidentes->documentos): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark"><tr>
          <th>Fecha</th><th>Nombre</th><th>Email</th><th>Tipo</th><th>Prioridad</th><th>Descripción</th><th>Ubicación</th><th>Estado</th>
        </tr></thead>
        <tbody>
          <?php foreach ($incidentes->documentos as $doc): ?>
            <tr>
              <td><?php echo $doc['fecha_registro'] ?? 'N/A'; ?></td>
              <td><?php echo htmlspecialchars($doc['nombre']); ?></td>
              <td><?php echo htmlspecialchars($doc['email']); ?></td>
              <td><?php echo ucfirst($doc['tipo_incidente']); ?></td>
              <td><span class="badge bg-info"><?php echo ucfirst($doc['prioridad']); ?></span></td>
              <td><?php echo htmlspecialchars(substr($doc['descripcion'], 0, 50)) . '...'; ?></td>
              <td><?php echo htmlspecialchars($doc['ubicacion']); ?></td>
              <td><span class="badge bg-secondary"><?php echo ucfirst($doc['estado']); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-center text-muted">😔 No hay incidentes registrados aún</p>
  <?php endif; ?>
</div>
</body>
</html>
