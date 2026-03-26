-- -----------------------------------------------------
-- Script SQL para la Base de Datos de Gestión de Practicantes (v4)
-- -----------------------------------------------------
-- Se recomienda usar ENGINE=InnoDB para asegurar el soporte de Foreign Keys.
-- El orden de creación es importante para respetar las dependencias.

-- -----------------------------------------------------
-- 1. TABLAS CATÁLOGO (Maestras sin dependencias)
-- -----------------------------------------------------

-- Para 'LOCALL'
CREATE TABLE Locales (
    local_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- Para 'LUGAR'
CREATE TABLE Areas (
    area_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Universidades (
    universidad_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 2. TABLAS CATÁLOGO (Maestras con dependencias)
-- -----------------------------------------------------

CREATE TABLE EscuelasProfesionales (
    escuela_id INT PRIMARY KEY AUTO_INCREMENT,
    universidad_id INT,
    nombre VARCHAR(150) NOT NULL, -- (Escuela Profesional de Derecho)
    
    FOREIGN KEY (universidad_id) REFERENCES Universidades(universidad_id)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 3. TABLAS CENTRALES (Núcleo del sistema)
-- -----------------------------------------------------

-- Tabla: Practicantes (La persona)
CREATE TABLE Practicantes (
    practicante_id INT PRIMARY KEY AUTO_INCREMENT,
    dni VARCHAR(15) UNIQUE NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    email VARCHAR(100),
    telefono VARCHAR(20),
    promedio_general DECIMAL(4, 2),
    
    -- Estado maestro de la persona en la organización
    estado_general VARCHAR(30) NOT NULL DEFAULT 'Candidato', -- (Candidato, Activo, Cesado)
    
    -- Llave Foránea (FK) a tabla catálogo
    escuela_profesional_id INT,
    
    FOREIGN KEY (escuela_profesional_id) REFERENCES EscuelasProfesionales(escuela_id)
) ENGINE=InnoDB;

-- Tabla: ProcesosReclutamiento (La postulación)
CREATE TABLE ProcesosReclutamiento (
    proceso_id INT PRIMARY KEY AUTO_INCREMENT,
    practicante_id INT NOT NULL,
    fecha_postulacion DATE,
    fecha_entrevista DATE,
    
    -- Puntuación calculada de la tabla ResultadosEntrevista
    puntuacion_final_entrevista DECIMAL(4, 2),
    
    estado_proceso VARCHAR(30) NOT NULL, -- (En Evaluación, Aceptado, Rechazado)
    
    FOREIGN KEY (practicante_id) REFERENCES Practicantes(practicante_id)
) ENGINE=InnoDB;

-- Tabla: ResultadosEntrevista (Las notas)
CREATE TABLE ResultadosEntrevista (
    resultado_id INT PRIMARY KEY AUTO_INCREMENT,
    proceso_id INT NOT NULL,
    
    -- Los 10 campos dinámicos
    campo_1_nombre VARCHAR(50) DEFAULT 'Criterio 1',
    campo_1_nota DECIMAL(4, 2),
    campo_2_nombre VARCHAR(50) DEFAULT 'Criterio 2',
    campo_2_nota DECIMAL(4, 2),
    campo_3_nombre VARCHAR(50) DEFAULT 'Criterio 3',
    campo_3_nota DECIMAL(4, 2),
    campo_4_nombre VARCHAR(50) DEFAULT 'Criterio 4',
    campo_4_nota DECIMAL(4, 2),
    campo_5_nombre VARCHAR(50) DEFAULT 'Criterio 5',
    campo_5_nota DECIMAL(4, 2),
    campo_6_nombre VARCHAR(50) DEFAULT 'Criterio 6',
    campo_6_nota DECIMAL(4, 2),
    campo_7_nombre VARCHAR(50) DEFAULT 'Criterio 7',
    campo_7_nota DECIMAL(4, 2),
    campo_8_nombre VARCHAR(50) DEFAULT 'Criterio 8',
    campo_8_nota DECIMAL(4, 2),
    campo_9_nombre VARCHAR(50) DEFAULT 'Criterio 9',
    campo_9_nota DECIMAL(4, 2),
    campo_10_nombre VARCHAR(50) DEFAULT 'Criterio 10',
    campo_10_nota DECIMAL(4, 2),
    
    comentarios_adicionales TEXT,
    
    FOREIGN KEY (proceso_id) REFERENCES ProcesosReclutamiento(proceso_id)
) ENGINE=InnoDB;

-- Tabla: Convenios (El contrato/expediente maestro)
CREATE TABLE Convenios (
    convenio_id INT PRIMARY KEY AUTO_INCREMENT,
    practicante_id INT NOT NULL,
    proceso_id INT UNIQUE, -- Un proceso de selección solo puede generar un convenio
    
    tipo_practica VARCHAR(50) NOT NULL,
    
    -- ¡MODIFICADO! Añadido estado 'Renuncia'
    estado_convenio VARCHAR(30) NOT NULL, -- (Vigente, Finalizado, Cancelado, Renuncia)
    
    induccion_completada BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (practicante_id) REFERENCES Practicantes(practicante_id),
    FOREIGN KEY (proceso_id) REFERENCES ProcesosReclutamiento(proceso_id)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 4. TABLAS DE HISTORIAL Y ARCHIVOS (Dependientes)
-- -----------------------------------------------------

-- Tabla: PeriodosConvenio (El historial de movimientos)
CREATE TABLE PeriodosConvenio (
    periodo_id INT PRIMARY KEY AUTO_INCREMENT,
    convenio_id INT NOT NULL,
    
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    
    local_id INT,
    area_id INT,
    
    estado_periodo VARCHAR(30) NOT NULL, -- (Finalizado, Activo, Futuro)
    
    FOREIGN KEY (convenio_id) REFERENCES Convenios(convenio_id),
    FOREIGN KEY (local_id) REFERENCES Locales(local_id),
    FOREIGN KEY (area_id) REFERENCES Areas(area_id)
) ENGINE=InnoDB;

-- Tabla: Adendas (La justificación de los cambios)
CREATE TABLE Adendas (
    adenda_id INT PRIMARY KEY AUTO_INCREMENT,
    convenio_id INT NOT NULL,
    
    tipo_accion VARCHAR(50), -- (CORTE, AMPLIACION, REUBICACION) 
    fecha_adenda DATE,
    descripcion TEXT,
    
    FOREIGN KEY (convenio_id) REFERENCES Convenios(convenio_id)
) ENGINE=InnoDB;

-- Tabla: Documentos (El archivador digital)
CREATE TABLE Documentos (
    documento_id INT PRIMARY KEY AUTO_INCREMENT,
    practicante_id INT NOT NULL,
    convenio_id INT,
    adenda_id INT,
    
    -- ¡MODIFICADO! Añadido tipo 'RENUNCIA_SEGURO' como ejemplo
    tipo_documento VARCHAR(50) NOT NULL, -- (CV, DNI, CONVENIO, ADENDA, FICHA_CALIFICACION, CARTA_PRESENTACION, DECLARACIONES, RENUNCIA_SEGURO)
    
    url_archivo VARCHAR(255) NOT NULL,
    fecha_carga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (practicante_id) REFERENCES Practicantes(practicante_id),
    FOREIGN KEY (convenio_id) REFERENCES Convenios(convenio_id),
    FOREIGN KEY (adenda_id) REFERENCES Adendas(adenda_id)
) ENGINE=InnoDB;

