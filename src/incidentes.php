<?php
namespace MiProyecto;

use MongoDB\Client;

class Incidentes {
    private $coleccion;
    public $mensaje = "";
    public $tipoMensaje = "";
    public $documentos = [];

    public function __construct() {
        try {
            $mongoUri = getenv('MONGODB_URI') ?: "mongodb+srv://incidentes_dbsebastian:12345678910@incidentes.un8zeze.mongodb.net/?appName=incidentes";
            $cliente = new Client($mongoUri);
            $db = $cliente->incidentes;
            $this->coleccion = $db->reportes;
        } catch (\Exception $e) {
            $this->mensaje = "❌ Error de conexión: " . $e->getMessage();
            $this->tipoMensaje = "danger";
        }
    }

    public function procesarFormulario($data) {
        $nombre = trim($data['nombre'] ?? '');
        $email = trim($data['email'] ?? '');
        $tipo = trim($data['tipo_incidente'] ?? '');
        $prioridad = trim($data['prioridad'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $ubicacion = trim($data['ubicacion'] ?? '');

        if (!$nombre || !$email || !$tipo || !$prioridad || !$descripcion) {
            $this->mensaje = "❌ Por favor complete todos los campos obligatorios";
            $this->tipoMensaje = "danger";
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->mensaje = "❌ Email inválido";
            $this->tipoMensaje = "danger";
            return;
        }

        $documento = [
            "nombre" => $nombre,
            "email" => $email,
            "tipo_incidente" => $tipo,
            "prioridad" => $prioridad,
            "descripcion" => $descripcion,
            "ubicacion" => $ubicacion,
            "fecha_registro" => date('Y-m-d H:i:s'),
            "estado" => "pendiente"
        ];

        $insertar = $this->coleccion->insertOne($documento);

        if ($insertar->getInsertedId()) {
            $this->mensaje = "✅ Incidente registrado exitosamente con código: " . $insertar->getInsertedId();
            $this->tipoMensaje = "success";
        } else {
            $this->mensaje = "❌ Error al registrar el incidente";
            $this->tipoMensaje = "danger";
        }
    }

    public function obtenerReportes() {
        $consulta = $this->coleccion->find([], ['sort' => ['fecha_registro' => -1], 'limit' => 100]);
        $this->documentos = $consulta->toArray();
    }
}
