<?php
// controllers/PersonaController.php
require_once 'models/Persona.php';

class PersonaController {

    private $db;
    private $personaModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->personaModel = new Persona($this->db);
    }

    // --- ACCIÓN INDEX (READ) ---
    // Muestra el listado de personas
    public function index() {
        $listaPersonas = $this->personaModel->listar();
        require 'views/layout/header.php';
        require 'views/personas/index.php';
        require 'views/layout/footer.php';
    }

    // --- ACCIÓN CREATE (Formulario) ---
    // Muestra el formulario para crear una nueva persona
    public function create() {
        require 'views/layout/header.php';
        require 'views/personas/create.php';
        require 'views/layout/footer.php';
    }

    // --- ACCIÓN STORE (Guardar) ---
    // Recibe los datos del formulario 'create' y los guarda en la BD
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recoger datos del formulario
            $datos = [
                'dni' => $_POST['dni'] ?? null,
                'numero_empleado' => $_POST['numero_empleado'] ?? null,
                'nombre_completo' => $_POST['nombre_completo'] ?? '',
                'cargo' => $_POST['cargo'] ?? null,
                'area' => $_POST['area'] ?? null,
                'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null,
                'estado' => $_POST['estado'] ?? 'ACTIVO'
            ];

            // (Aquí iría la validación de datos)

            if ($this->personaModel->crear($datos)) {
                // Redirigir al listado con un mensaje de éxito (opcional)
                header('Location: index.php?controller=persona&action=index&status=creado');
            } else {
                // Manejar error
                echo "Error al crear la persona.";
            }
        }
    }

    // --- ACCIÓN EDIT (Formulario) ---
    // Muestra el formulario para editar una persona existente
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('Error: ID de persona no proporcionado.');
        }
        
        // Obtener los datos de la persona por su ID
        $persona = $this->personaModel->obtenerPorId($id);
        
        if (!$persona) {
            die('Error: Persona no encontrada.');
        }

        // Cargar la vista de edición y pasarle los datos
        require 'views/layout/header.php';
        require 'views/personas/edit.php'; // Esta vista usará $persona
        require 'views/layout/footer.php';
    }

    // --- ACCIÓN UPDATE (Actualizar) ---
    // Recibe los datos del formulario 'edit' y actualiza la BD
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                die('Error: ID no proporcionado para actualizar.');
            }

            $datos = [
                'dni' => $_POST['dni'] ?? null,
                'numero_empleado' => $_POST['numero_empleado'] ?? null,
                'nombre_completo' => $_POST['nombre_completo'] ?? '',
                'cargo' => $_POST['cargo'] ?? null,
                'area' => $_POST['area'] ?? null,
                'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null,
                'estado' => $_POST['estado'] ?? 'ACTIVO'
            ];

            if ($this->personaModel->actualizar($id, $datos)) {
                header('Location: index.php?controller=persona&action=index&status=actualizado');
            } else {
                echo "Error al actualizar la persona.";
            }
        }
    }

    // --- ACCIÓN DELETE (Borrar) ---
    // Borra una persona por su ID
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('Error: ID no proporcionado para eliminar.');
        }

        if ($this->personaModel->eliminar($id)) {
            header('Location: index.php?controller=persona&action=index&status=eliminado');
        } else {
            echo "Error al eliminar la persona.";
        }
    }
}