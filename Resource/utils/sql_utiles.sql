-- Consultas SQL Esenciales
-- 1. Costo Total de un Evento (Incluyendo Viáticos, Insumos y Monotributo)
-- Esta consulta suma todos los registros de la tabla gastos que están asociados a un evento específico.

SQL

SELECT
    E.NombreEvento,
    E.Contratista,
    SUM(G.Monto) AS CostoTotalEvento
FROM
    gastos G
INNER JOIN
    eventos E ON G.ID_Evento = E.ID_Evento
WHERE
    G.ID_Evento = 123 -- Reemplaza '123' con el ID del evento que deseas analizar
GROUP BY
    E.NombreEvento, E.Contratista;

-- 2. Detalle del Gasto por Categoría en un Evento
-- Esta consulta desglosa cuánto se gastó en cada tipo de categoría (ej. Viáticos, Insumos, etc.) para un evento específico.

SQL

SELECT
    C.NombreCategoria,
    SUM(G.Monto) AS MontoTotal
FROM
    gastos G
INNER JOIN
    categorias_gasto C ON G.ID_Categoria = C.ID_Categoria
WHERE
    G.ID_Evento = 123 -- Reemplaza '123' con el ID del evento que deseas
GROUP BY
    C.NombreCategoria
ORDER BY
    MontoTotal DESC;
3. Costo Laboral Total por Evento (Usando la Vista)
La vista vw_costoslaborales_evento ya tiene precalculada esta información, lo que simplifica la consulta.

SQL

SELECT
    NombreEvento,
    Localidad,
    TotalHorasAsignadas,
    CostoMensualEstimado AS CostoLaboralEstimado
FROM
    vw_costoslaborales_evento
ORDER BY
    CostoLaboralEstimado DESC;

-- 4. Empleados Asignados y Horas por Evento
-- Esta consulta te permite saber qué empleados trabajaron en un evento y cuántas horas fueron asignadas (útil para la auditoría de nómina).

SQL

SELECT
    EVE.NombreEvento,
    EMP.Nombre,
    EMP.Apellido,
    EE.HorasAsignadas
FROM
    empleados_eventos EE
INNER JOIN
    empleados EMP ON EE.ID_Empleado = EMP.ID_Empleado
INNER JOIN
    eventos EVE ON EE.ID_Evento = EVE.ID_Evento
WHERE
    EVE.ID_Evento = 123; -- Reemplaza '123' con el ID del evento

-- 5. Rentabilidad de Eventos (Costo vs. Ingreso Estimado)
-- Esta consulta calcula la ganancia/pérdida (rentabilidad) de cada evento, restando el costo total (asumiendo que los gastos son el costo total del evento) del monto que se espera cobrar.

SQL

SELECT
    E.ID_Evento,
    E.NombreEvento,
    E.MontoCobrarEstimado AS IngresoEstimado,
    SUM(G.Monto) AS CostoTotalReal,
    (E.MontoCobrarEstimado - SUM(G.Monto)) AS Rentabilidad
FROM
    eventos E
LEFT JOIN
    gastos G ON E.ID_Evento = G.ID_Evento
GROUP BY
    E.ID_Evento, E.NombreEvento, E.MontoCobrarEstimado
ORDER BY
    Rentabilidad DESC;