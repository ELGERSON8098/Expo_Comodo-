<?php
// Se incluye la clase para trabajar con la base de datos.
require_once ('../../helpers/database.php');
/*
 *  Clase para manejar el comportamiento de los datos de la tabla administrador.
 */
class marcaHandler
{
    /*
     *  Declaración de atributos para el manejo de datos.
     */
    protected $id = null;
    protected $nombre = null;

    
    protected $fecha_inicio = null;
    protected $fecha_fin = null;

    /*
     *  Métodos para realizar las operaciones SCRUD (search, create, read, update, and delete).
     */
    // Busca marcas en la base de datos por nombre.
    public function searchRows()
    {
        $value = '%' . Validator::getSearchValue() . '%';
        $sql = 'SELECT id_marca, marca
                FROM tb_marcas
                WHERE marca LIKE ?
                ORDER BY marca';
        $params = array($value);
        return Database::getRows($sql, $params);
    }
    //Crea una nueva marca en la base de datos.
    public function createRow()
    {
        $sql = 'INSERT INTO tb_marcas(marca)
                VALUES(?)';
        $params = array($this->nombre);
        return Database::executeRow($sql, $params);
    }

    //Llamar los datos de la base de datos 
    public function readAll()
    {
        $sql = 'SELECT id_marca, marca
            FROM tb_marcas
            ORDER BY marca ASC';
        return Database::getRows($sql);
    }
    //Genera un reporte predictivo de ventas por marca.
    public function ReportePredictivo()
    {
        // SQL Query
        $sql = '
        WITH VentasMensuales AS (
    SELECT 
        m.marca AS NombreMarca,
        p.nombre_producto AS NombreProducto,
        SUM(dr.cantidad) AS CantidadReservada,
        DATE_FORMAT(r.fecha_reserva, "%Y-%m") AS Mes,
        SUM(
            dr.cantidad * dr.precio_unitario * 
            (1 - IFNULL(d.valor, 0) / 100)
        ) AS TotalVentasMarca,
        (SUM(
            dr.cantidad * dr.precio_unitario * 
            (1 - IFNULL(d.valor, 0) / 100)
        ) * 100) / (
            SELECT SUM(
                dr2.cantidad * dr2.precio_unitario * 
                (1 - IFNULL(d2.valor, 0) / 100)
            )
            FROM tb_detalles_reservas dr2
            INNER JOIN tb_reservas r2 ON dr2.id_reserva = r2.id_reserva
            LEFT JOIN tb_productos p2 ON dr2.id_detalle_producto = p2.id_producto
            LEFT JOIN tb_descuentos d2 ON p2.id_descuento = d2.id_descuento
            WHERE r2.estado_reserva = "Aceptado"
            AND DATE_FORMAT(r2.fecha_reserva, "%Y-%m") = DATE_FORMAT(r.fecha_reserva, "%Y-%m")
        ) AS PorcentajeVentasMarca
    FROM 
        tb_marcas m
    INNER JOIN 
        tb_productos p ON m.id_marca = p.id_marca
    INNER JOIN 
        tb_detalles_productos dp ON p.id_producto = dp.id_producto
    INNER JOIN 
        tb_detalles_reservas dr ON dp.id_detalle_producto = dr.id_detalle_producto
    INNER JOIN 
        tb_reservas r ON dr.id_reserva = r.id_reserva
    LEFT JOIN 
        tb_descuentos d ON p.id_descuento = d.id_descuento
    WHERE 
        r.estado_reserva = "Aceptado"
    GROUP BY 
        NombreMarca, NombreProducto, Mes, r.fecha_reserva
),
VentasAnteriores AS (
    SELECT 
        NombreMarca,
        NombreProducto,
        AVG(TotalVentasMarca) AS PromedioMensual
    FROM 
        VentasMensuales
    GROUP BY 
        NombreMarca, NombreProducto
),
VentasActuales AS (
    SELECT 
        v1.NombreMarca,
        v1.NombreProducto,
        v1.Mes AS MesActual,
        v1.CantidadReservada,
        v1.TotalVentasMarca,
        v1.PorcentajeVentasMarca,
        DATE_FORMAT(DATE_ADD(STR_TO_DATE(v1.Mes, "%Y-%m-01"), INTERVAL 1 MONTH), "%Y-%m") AS MesSiguiente
    FROM 
        VentasMensuales v1
),
VentasPronosticadas AS (
    SELECT
        va.NombreMarca,
        va.NombreProducto,
        va.MesActual,
        va.CantidadReservada,
        va.TotalVentasMarca,
        va.PorcentajeVentasMarca,
        COALESCE(va_prev.PromedioMensual, 0) AS PromedioMensual,
        COALESCE(
            (va.PorcentajeVentasMarca / 100) * va_prev.PromedioMensual,
            0
        ) AS PrediccionVentasSiguienteMes
    FROM 
        VentasActuales va
    LEFT JOIN 
        VentasAnteriores va_prev 
        ON va.NombreMarca = va_prev.NombreMarca
        AND va.NombreProducto = va_prev.NombreProducto
)
SELECT 
    vp.NombreMarca,
    vp.NombreProducto,
    vp.MesActual,
    vp.CantidadReservada,
    vp.TotalVentasMarca,
    vp.PorcentajeVentasMarca,
    vp.PrediccionVentasSiguienteMes,
    CASE 
        WHEN vp.TotalVentasMarca = 0 THEN NULL
        ELSE
            CASE 
                WHEN vp.PrediccionVentasSiguienteMes > vp.TotalVentasMarca THEN
                    CONCAT(
                        "Aumento estimado de ", 
                        LEAST(
                            FORMAT((vp.PrediccionVentasSiguienteMes - vp.TotalVentasMarca) / vp.TotalVentasMarca * 100, 2), 
                            100
                        ), 
                        "%"
                    )
                ELSE
                    CONCAT(
                        "Reducción estimada de ", 
                        FORMAT(
                            LEAST(
                                (vp.TotalVentasMarca - vp.PrediccionVentasSiguienteMes) / vp.TotalVentasMarca * 100, 
                                100
                            ), 
                            2
                        ), 
                        "%"
                    )
            END
    END AS PorcentajeYMensaje
FROM 
    VentasPronosticadas vp
ORDER BY 
    vp.MesActual ASC, vp.NombreMarca ASC, vp.NombreProducto ASC;';
        
        // Execute the query
        return Database::getRows($sql);
    }
    
    //Lee los detalles de una marca específica por ID.
    public function readOne()
    {
        $sql = 'SELECT id_marca, marca
                FROM tb_marcas
                WHERE id_marca = ?';
        $params = array($this->id);
        return Database::getRow($sql, $params);
    }
    //Actualiza la información de una marca específica.
    public function updateRow()
    {
        $sql = 'UPDATE tb_marcas
                SET marca = ?
                WHERE id_marca = ?';
        $params = array($this->nombre, $this->id);
        return Database::executeRow($sql, $params);
    }

    public function getNombreMarca() {
        $sql = 'SELECT marca FROM tb_marcas WHERE id_marca = ?';
        $params = array($this->id);

        // Ejecutar la consulta
        if ($data = Database::getRow($sql, $params)) {
            $this->nombre = $data['marca'];
            return $this->nombre;
        } else {
            return null; // Si no se encuentra la marca
        }
    }

    //Elimina una marca específica por ID.
    public function deleteRow()
    {
        $sql = 'DELETE FROM tb_marcas
                WHERE id_marca = ?';
        $params = array($this->id);
        return Database::executeRow($sql, $params);
    }
   // Obtiene ventas por marcas dentro de un rango de fechas.
    public function ventasPorMarcasFecha($fecha_inicio, $fecha_fin) {
        $sql = 'SELECT 
    m.marca AS nombre_marca, 
    r.fecha_reserva, 
    SUM(dr.cantidad * dr.precio_unitario) AS total_ventas
    FROM 
    tb_detalles_reservas dr
    INNER JOIN 
    tb_reservas r ON dr.id_reserva = r.id_reserva
    INNER JOIN 
    tb_detalles_productos dp ON dr.id_detalle_producto = dp.id_detalle_producto
    INNER JOIN 
    tb_productos p ON dp.id_producto = p.id_producto
    INNER JOIN 
    tb_marcas m ON p.id_marca = m.id_marca
    WHERE 
    r.fecha_reserva BETWEEN ? AND ?
    GROUP BY 
    m.marca, r.fecha_reserva
    ORDER BY 
    r.fecha_reserva ASC
    LIMIT 5;';
        $params = array($fecha_inicio, $fecha_fin);
        return Database::getRows($sql, $params);
    }
}
