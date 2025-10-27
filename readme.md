Lógica del Sistema de Gestión de Practicantes

Este documento explica el flujo de trabajo y la lógica de la base de datos diseñada (v4).

1. El Núcleo: Practicantes

Todo gira en torno a la tabla Practicantes. Esta tabla almacena la información única de una persona (DNI, nombres, promedio, etc.).

Punto Clave: Contiene el estado_general ('Candidato', 'Activo', 'Cesado'). Este es el estado maestro de la persona en la organización, independientemente de si su convenio actual está en pausa o no.

2. El Flujo de Ingreso: ProcesosReclutamiento

Una persona puede postular muchas veces a diferentes puestos. Cada postulación es un registro en ProcesosReclutamiento.

Esta tabla se vincula al Practicante (para saber quién postula).

Contiene el estado_proceso ('En Evaluación', 'Aceptado', 'Rechazado').

Contiene la puntuacion_final_entrevista, que es el promedio calculado de las notas almacenadas en ResultadosEntrevista.

3. El Expediente: Convenios

Si un ProcesoReclutamiento es 'Aceptado', el sistema te debe permitir crear un Convenio.

El Convenio es el "expediente maestro" o "contrato macro".

Se vincula al Practicante y al ProcesoReclutamiento que lo originó.

Define el tipo_practica (Pre-pro, Pro) y el estado_convenio ('Vigente', 'Finalizado', 'Cancelado', 'Renuncia').

4. El Historial (La "Bitácora"): PeriodosConvenio

Esta es la parte clave para manejar tu historial complejo (cortes, reubicaciones). Un convenio puede tener múltiples períodos.

Caso Simple: Un convenio sin adendas tendrá 1 solo registro en esta tabla (ej: 10-jul al 10-nov en Jurídica).

Caso Complejo (Corte): Un convenio con un corte tendrá 2 (o más) registros:

periodo_id = 1: 10-jul al 31-jul en Jurídica | estado_periodo = 'Finalizado'

periodo_id = 2: 01-sep al 10-dic en Jurídica | estado_periodo = 'Activo' (o 'Futuro' si aún no es sep).

Caso Complejo (Reubicación): Similar, pero cambiaría el area_id:

periodo_id = 1: 10-jul al 31-ago en Jurídica | estado_periodo = 'Finalizado'

periodo_id = 2: 01-sep al 10-nov en Contabilidad | estado_periodo = 'Activo'

El estado_periodo ('Activo', 'Finalizado', 'Futuro') es crucial para saber dónde debería estar el practicante en un día específico.

5. El Disparador: Adendas

Las Adendas son el porqué de los cambios.

Cuando registras una adenda (ej: 'CORTE'), el sistema sabe que debe ir a la tabla PeriodosConvenio para "cerrar" el período activo actual y, si es necesario, crear uno nuevo 'Futuro'.

La adenda no modifica el convenio directamente; justifica la modificación del historial (PeriodosConvenio).

6. Archivos y Catálogos

Documentos: Es el archivador digital. Guarda las rutas de los PDFs (DNI, CVs, Convenios, Adendas firmadas, 'RENUNCIA_SEGURO', etc.).

Locales, Areas, Escuelas, Universidades: Son las "tablas maestras" para rellenar tus listas desplegables y asegurar que los datos sean consistentes (evitar escribir "Juridica", "Jurídica", "OF. JURIDICA").

7. ¡NUEVO! Gestión de Procesos Especiales

¿Cómo gestionar el Seguro?

Para gestionar si un practicante tiene o no seguro, se usa la tabla Documentos:

Se asume que todos tienen seguro por defecto.

Si un practicante presenta una renuncia a su seguro, se debe subir ese archivo a la tabla Documentos con el tipo_documento = 'RENUNCIA_SEGURO', vinculado a su practicante_id.

El sistema puede verificar fácilmente si un practicante tiene seguro o no buscando si existe un documento de ese tipo para él.

¿Cómo gestionar una Renuncia?

Si un practicante renuncia en cualquier momento, el flujo en el sistema es el siguiente:

Actualizar el Período Activo: Se busca en la tabla PeriodosConvenio el período que esté 'Activo' para ese convenio. Se edita su fecha_fin a la fecha de renuncia y se cambia su estado_periodo a 'Finalizado'.

Actualizar el Convenio: Se va a la tabla Convenios y se actualiza el estado_convenio a 'Renuncia'.

Actualizar al Practicante: Finalmente, se actualiza el estado_general en la tabla Practicantes a 'Cesado'.