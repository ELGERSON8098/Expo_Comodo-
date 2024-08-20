// Constante para completar la ruta de la API.
const PRODUCTO_API = 'services/admin/producto.php';
const RESERVA_API = 'services/admin/reserva.php';

// Método del evento para cuando el documento ha cargado.
document.addEventListener('DOMContentLoaded', () => {
    // Constante para obtener el número de horas.
    const HOUR = new Date().getHours();
    // Se define una variable para guardar un saludo.
    let greeting = '';
    // Dependiendo del número de horas transcurridas en el día, se asigna un saludo para el usuario.
    if (HOUR < 12) {
        greeting = 'Buenos días';
    } else if (HOUR < 19) {
        greeting = 'Buenas tardes';
    } else if (HOUR <= 23) {
        greeting = 'Buenas noches';
    }
    // Llamada a la función para mostrar el encabezado y pie del documento.
    loadTemplate();
    // Se establece el título del contenido principal.
    MAIN_TITLE.textContent = `${greeting}, bienvenido`;
    // Llamada a las funciones que generan los gráficos en la página web.
    graficoBarrasCategorias();
    graficoPastelCategorias();
    graficoLineasCategorias();
    graficoRadarCategorias();
    graficoPolarCategorias();
    graficoTortaGeneros();
    graficoTortaReservas();
    graficaVentasPrediccion();
});

/*
*   Función asíncrona para mostrar un gráfico de barras con la cantidad de productos por categoría.
*   Parámetros: ninguno.
*   Retorno: ninguno.
*/
const graficoBarrasCategorias = async () => {
    const DATA = await fetchData(PRODUCTO_API, 'cantidadProductosCategoria');
    if (DATA.status) {
        let categorias = [];
        let cantidades = [];
        DATA.dataset.forEach(row => {
            categorias.push(row.nombre_categoria);
            cantidades.push(row.cantidad);
        });
        barGraph('chart1', categorias, cantidades, 'Cantidad de productos', 'Cantidad de productos por categoría');
    } else {
        document.getElementById('chart1').remove();
        console.log(DATA.error);
    }
}

/*
*   Función asíncrona para mostrar un gráfico de pastel con el porcentaje de productos por categoría.
*   Parámetros: ninguno.
*   Retorno: ninguno.
*/
const graficoPastelCategorias = async () => {
    const DATA = await fetchData(PRODUCTO_API, 'porcentajeProductosCategoria');
    if (DATA.status) {
        let categorias = [];
        let porcentajes = [];
        DATA.dataset.forEach(row => {
            categorias.push(row.nombre_categoria);
            porcentajes.push(row.porcentaje);
        });
        pieGraph('chart2', categorias, porcentajes, 'Porcentaje de productos por categoría');
    } else {
        document.getElementById('chart2').remove();
        console.log(DATA.error);
    }
}

/*
*   Función asíncrona para mostrar un gráfico de líneas con la cantidad de productos por categoría.
*   Parámetros: ninguno.
*   Retorno: ninguno.
*/
const graficoLineasCategorias = async () => {
    const DATA = await fetchData(PRODUCTO_API, 'descuentosMasUtilizados');
    if (DATA.status) {
        let descuentos = [];
        let cantidades = [];
        DATA.dataset.forEach(row => {
            descuentos.push(row.nombre_descuento);
            cantidades.push(row.cantidad);
        });
        lineGraph('chart3', descuentos, cantidades, 'Cantidad de productos', 'Descuentos más utilizados');
    } else {
        document.getElementById('chart3').remove();
        console.log(DATA.error);
    }
}

/*
*   Función asíncrona para mostrar un gráfico de radar con la cantidad de productos por categoría.
*   Parámetros: ninguno.
*   Retorno: ninguno.
*/
const graficoRadarCategorias = async () => {
    const DATA = await fetchData(PRODUCTO_API, 'marcaMasComprada');
    if (DATA.status) {
        let marcas = [];
        let cantidades = [];
        DATA.dataset.forEach(row => {
            marcas.push(row.marca);
            cantidades.push(row.cantidad);
        });
        radarGraph('chart4', marcas, cantidades, 'Cantidad de productos vendidos', 'Marca más comprada');
    } else {
        document.getElementById('chart4').remove();
        console.log(DATA.error);
    }
}

/*
*   Función asíncrona para mostrar un gráfico polar con la cantidad de productos por categoría.
*   Parámetros: ninguno.
*   Retorno: ninguno.
*/
const graficoPolarCategorias = async () => {
    const DATA = await fetchData(PRODUCTO_API, 'productosMasVendidosPorCategoria');
    if (DATA.status) {
        let categorias = [];
        let cantidades = [];
        DATA.dataset.forEach(row => {
            categorias.push(row.nombre_categoria);
            cantidades.push(row.cantidad);
        });
        polarGraph('chart5', categorias, cantidades, 'Cantidad de productos vendidos', 'Productos más vendidos por categoría');
    } else {
        document.getElementById('chart5').remove();
        console.log(DATA.error);
    }
}

const graficoTortaGeneros = async () => {
    // Petición para obtener los datos del gráfico.
    const DATA = await fetchData(PRODUCTO_API, 'cantidadProductosGenero');
    
    // Se comprueba si la respuesta es satisfactoria, de lo contrario se remueve la etiqueta canvas.
    if (DATA.status) {
        let generos = [];
        let cantidades = [];
        
        // Se recorre el conjunto de registros fila por fila a través del objeto row.
        DATA.dataset.forEach(row => {
            generos.push(row.nombre_genero);
            cantidades.push(row.cantidad);
        });
        
        // Llamada a la función para generar y mostrar un gráfico de torta.
        pieGraph('chart6', generos, cantidades, 'Distribución de productos por género');
    } else {
        document.getElementById('chart6').remove();
        console.log(DATA.error);
    }
}

const graficoTortaReservas = async () => {
    // Petición para obtener los datos del gráfico.
    const DATA = await fetchData(RESERVA_API, 'cantidadReservasEstado');
    
    // Se comprueba si la respuesta es satisfactoria, de lo contrario se remueve la etiqueta canvas.
    if (DATA.status) {
        let estados = [];
        let cantidades = [];
        
        // Se recorre el conjunto de registros fila por fila a través del objeto row.
        DATA.dataset.forEach(row => {
            estados.push(row.estado_reserva);
            cantidades.push(row.cantidad);
        });
        
        // Llamada a la función para generar y mostrar un gráfico de torta.
        polarGraph('chart7', estados, cantidades, 'Distribución de reservas por estado');
    } else {
        document.getElementById('chart7').remove();
        console.log(DATA.error);
    }
}

const graficaVentasPrediccion = async () => {
    const DATA = await fetchData(PRODUCTO_API, 'ventasUltimosSeisMeses');
    if (DATA.status) {
        let meses = [];
        let ventas = [];
        const ordenMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
 
        // Ordenar los datos según el orden de los meses
        DATA.dataset.sort((a, b) => ordenMeses.indexOf(a.mes) - ordenMeses.indexOf(b.mes));
 
        DATA.dataset.forEach(row => {
            meses.push(row.mes);
            ventas.push(parseFloat(row.ventas_totales));
        });
 
        // Convertir los datos de ventas a un tensor
        const xs = tf.tensor1d(ventas.map((_, i) => i));
        const ys = tf.tensor1d(ventas);
 
        // Definir un modelo de regresión lineal
        const model = tf.sequential();
        model.add(tf.layers.dense({units: 1, inputShape: [1]}));
 
        // Compilar el modelo
        model.compile({loss: 'meanSquaredError', optimizer: 'sgd'});
 
        // Entrenar el modelo
await model.fit(xs, ys, {epochs: 500});
 
        // Hacer predicciones para los próximos 3 meses
        const numMeses = ventas.length;
        let predicciones = [];
        for (let i = numMeses; i < numMeses + 3; i++) {
            const prediccion = model.predict(tf.tensor2d([i], [1, 1]));
            predicciones.push(prediccion.dataSync()[0]);
        }
 
        // Añadir las predicciones a las ventas y los meses
        predicciones.forEach((venta, i) => {
            ventas.push(venta);
            const ultimoMesIndex = ordenMeses.indexOf(meses[meses.length - 1]);
            const nuevoMesIndex = (ultimoMesIndex + 1 + i) % 12;
            meses.push(ordenMeses[nuevoMesIndex]);
        });
 
        // Crear la gráfica
        const ctx = document.getElementById('chartVentas').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas reales',
                    data: ventas.slice(0, -3),
                    borderColor: 'blue',
                    fill: false
                }, {
                    label: 'Ventas proyectadas',
                    data: ventas.slice(-3),
                    borderColor: 'red',
                    borderDash: [5, 5],
                    fill: false
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Ventas reales y proyectadas'
                },
                legend: {
                    position: 'top',
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Mes'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Ventas ($)'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    } else {
        console.log(DATA.error);
    }
};