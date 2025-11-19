<?php
require 'vendor/autoload.php';
use MongoDB\Client;

$mensaje = "";
$tipoMensaje = "";
$documentos = [];

try {
    // Usar variable de entorno para la conexión
    $mongoUri = getenv('MONGODB_URI') ?: "mongodb+srv://incidentes_dbsebastian:12345678910@incidentes.un8zeze.mongodb.net/?appName=incidentes";
    $cliente = new Client($mongoUri);

    $db = $cliente->incidentes;
    $coleccion = $db->reportes;

    // Procesar formulario si se envió
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $tipo_incidente = isset($_POST['tipo_incidente']) ? trim($_POST['tipo_incidente']) : '';
        $prioridad = isset($_POST['prioridad']) ? trim($_POST['prioridad']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
        $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';

        if (empty($nombre) || empty($email) || empty($tipo_incidente) || empty($prioridad) || empty($descripcion)) {
            $mensaje = "❌ Por favor complete todos los campos obligatorios";
            $tipoMensaje = "danger";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "❌ Email inválido";
            $tipoMensaje = "danger";
        } else {
            $documento = [
                "nombre" => $nombre,
                "email" => $email,
                "tipo_incidente" => $tipo_incidente,
                "prioridad" => $prioridad,
                "descripcion" => $descripcion,
                "ubicacion" => $ubicacion,
                "fecha_registro" => date('Y-m-d H:i:s'),
                "estado" => "pendiente"
            ];

            $insertar = $coleccion->insertOne($documento);

            if ($insertar->getInsertedId()) {
                $mensaje = "✅ Incidente registrado exitosamente con código: " . $insertar->getInsertedId();
                $tipoMensaje = "success";
            } else {
                $mensaje = "❌ Error al registrar el incidente";
                $tipoMensaje = "danger";
            }
        }
    }

    $consulta = $coleccion->find([], [
        'sort' => ['fecha_registro' => -1],
        'limit' => 100
    ]);
    $documentos = $consulta->toArray();

} catch (Exception $e) {
    $mensaje = "❌ Error de conexión: " . $e->getMessage();
    $tipoMensaje = "danger";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Incidentes Técnicos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h1 class="text-center text-primary mb-4">🔧 Reporte de Incidentes</h1>
  <p class="text-center text-secondary">Sistema de registro técnico de fallos</p>

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> text-center">
      <?php echo $mensaje; ?>
    </div>
  <?php endif; ?>

  <form action="incidentes.php" method="post" class="p-4 bg-white shadow rounded mb-4">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre del Reportante *</label>
      <input type="text" id="nombre" name="nombre" required class="form-control">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email de Contacto *</label>
      <input type="email" id="email" name="email" required class="form-control">
    </div>
    <div class="mb-3">
      <label for="tipo_incidente" class="form-label">Tipo de Incidente *</label>
      <select id="tipo_incidente" name="tipo_incidente" required class="form-select">
        <option value="">Seleccione una opción</option>
        <option value="hardware">Hardware</option>
        <option value="software">Software</option>
        <option value="red">Red</option>
        <option value="seguridad">Seguridad</option>
        <option value="otro">Otro</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="prioridad" class="form-label">Prioridad *</label>
      <select id="prioridad" name="prioridad" required class="form-select">
        <option value="">Seleccione prioridad</option>
        <option value="baja">Baja</option>
        <option value="media">Media</option>
        <option value="alta">Alta</option>
        <option value="critica">Crítica</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="descripcion" class="form-label">Descripción *</label>
      <textarea id="descripcion" name="descripcion" required class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label for="ubicacion" class="form-label">Ubicación</label>
      <input type="text" id="ubicacion" name="ubicacion" class="form-control">
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-primary">📤 Enviar Reporte</button>
    </div>
  </form>

  <h2 class="text-primary">📋 Reportes registrados</h2>
  <?php if (!empty($documentos)): ?>
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Fecha</th>
            <th>Reportante</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Prioridad</th>
            <th>Descripción</th>
            <th>Ubicación</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($documentos as $doc): ?>
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