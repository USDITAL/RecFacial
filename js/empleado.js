// Habilitar/deshabilitar los campos de fecha según el filtro seleccionado
function toggleDateInputs() {
    const filterMode = document.getElementById('filter-mode').value;
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');

    if (filterMode === 'range') {
        startDate.disabled = false;
        endDate.disabled = false;
    } else {
        startDate.disabled = true;
        endDate.disabled = true;
    }
}

// Agregar un listener al DOM para el evento change del filtro
document.addEventListener('DOMContentLoaded', () => {
    const filterModeSelect = document.getElementById('filter-mode');
    
    if (filterModeSelect) {
        filterModeSelect.addEventListener('change', toggleDateInputs);
    }

    const contextMenu = document.getElementById('context-menu');
    const registroContainer = document.getElementById('registrosExportables');//.querySelector('.registro ul'); // Contenedor de los registros
    const bloqueRevision = document.getElementById('bloque-revision');
    const bloqueMostrarDatos = document.getElementById('bloque-mostrardatos');

    // Función para centrar elementos en la ventana
    function centrarElemento(elemento) {
        elemento.style.position = 'fixed';
        elemento.style.top = '50%';
        elemento.style.left = '50%';
        elemento.style.transform = 'translate(-50%, -50%)';
        elemento.style.zIndex = '1000';
    }

    // Configurar bloques flotantes
    [bloqueRevision, bloqueMostrarDatos].forEach(bloque => {
        centrarElemento(bloque);
        bloque.style.display = 'none';
        bloque.style.backgroundColor = 'white';
        bloque.style.padding = '20px';
        bloque.style.border = '1px solid #ccc';
        bloque.style.boxShadow = '0 0 10px rgba(0,0,0,0.1)';
    });

    //Evento filtrado de datos
    document.getElementById('filtroDatos').addEventListener('click', () => {
        const listadoDatos= document.getElementById('registrosExportables');
        const filterMode = filterModeSelect.value;
        const startDate = document.getElementById('start-date');
        const endDate = document.getElementById('end-date');
        const cod_empleado = document.getElementById('employee-name').getAttribute('data-id');
        const data = {
            accion: 'filtrar_datos',
            desdeFecha: startDate.value.toString(),
            hastaFecha: endDate.value.toString(),
            filtro: filterMode,
            empleado: cod_empleado
        };
        
        // Limpiar formulario
        listadoDatos.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data,'json')
        .then(respuesta =>{
            listadoDatos.innerHTML = respuesta.html;
            registros = respuesta.registros;
        });
        renderChart([], [], [], [], 0); // Limpiar la gráfica

        
    });
    
    // Eventos para cerrar los bloques
    document.getElementById('cerrar-revision').addEventListener('click', () => {
        bloqueRevision.style.display = 'none';
    });

    document.getElementById('cerrar-mostrardatos').addEventListener('click', () => {
        bloqueMostrarDatos.style.display = 'none';
    });

    
    // Ocultar menú y bloques al hacer clic fuera
    document.addEventListener('click', (event) => {
        const elementosInteractivos = [
            contextMenu, 
            bloqueRevision, 
            bloqueMostrarDatos,
            ...document.querySelectorAll('#context-menu *, #bloque-revision *, #bloque-mostrardatos *')
        ];
        
        const clickEnElementoInteractivo = elementosInteractivos.some(el => el.contains(event.target));
        const clickEnListItem = event.target.closest('.registro li') !== null;

        if (!clickEnElementoInteractivo && !clickEnListItem) {
            if (contextMenu.style.display === 'block') contextMenu.style.display = 'none';
            if (bloqueRevision.style.display === 'block') bloqueRevision.style.display = 'none';
            if (bloqueMostrarDatos.style.display === 'block') bloqueMostrarDatos.style.display = 'none';
        }
    });

    // Delegación de eventos: Escucha los clics en el contenedor
    registroContainer.addEventListener('click', (event) => {
        const target = event.target.closest('li'); // Busca el <li> más cercano al clic
        if (target) {
            event.preventDefault(); // Evita el comportamiento predeterminado

            // Obtén las coordenadas del clic
            const x = event.pageX;
            const y = event.pageY;

            // Posiciona el menú contextual
            contextMenu.style.left = `${x}px`;
            contextMenu.style.top = `${y}px`;
            contextMenu.style.display = 'block';

            // Guarda el registro seleccionado en un atributo de datos
            contextMenu.dataset.selectedRegistro = target.dataset.id;
            contextMenu.dataset.selectedRegistroFecha = target.dataset.fecha;
            
        }
    });
        
// Función para actualizar la hora en vivo
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').innerText = now.toLocaleTimeString();
}
setInterval(updateTime, 1000);
updateTime();


    // Manejar opciones del menú contextual
    document.getElementById('solicitar-revision').addEventListener('click', () => {
        const registroFecha = contextMenu.dataset.selectedRegistroFecha;
        const registroId = contextMenu.dataset.selectedRegistro;
        contextMenu.style.display = 'none';
        
        // Configurar el formulario de revisión con el ID del registro
        document.getElementById('registro-id-revision').value = registroId;
        document.getElementById('comentario-fecha').value = registroFecha;
        bloqueRevision.style.display = 'block';
    });

    document.getElementById('mostrar-datos').addEventListener('click', () => {
        const registroFecha = contextMenu.dataset.selectedRegistroFecha;
        contextMenu.style.display = 'none';
        
        // Buscar los marcajes para esta fecha
        const marcajesFecha = Object.values(todosMarcajes).filter(marcaje => {
            return marcaje.FEC_MARCAJE.startsWith(registroFecha);
        });
        const fechaHoy = new Date().toISOString().split('T')[0];
        
        // Generar el HTML
        let html = `
            <div class="section" id="recent-accesses">
                <h3>Marcajes del ${registroFecha}</h3>
                <ul>`;
        
        marcajesFecha.forEach(marcaje => {
            const fechaMarcaje = marcaje.FEC_MARCAJE.split(' ')[0]; // Obtener solo la fecha
            const esHoy = (fechaMarcaje === fechaHoy);
            const tipoClase = marcaje.COD_TIPO_MARCAJE == 1 ? "tipoA" : "tipoB";
            const tipoTexto = marcaje.COD_TIPO_MARCAJE == 1 ? "Entrada" : "Salida";
            const color = !esHoy ? 'darkgrey' : 'inherit';
            
            html += `
                <li class="acceso-item" style="color: ${color};">
                    <span class="${tipoClase}">${tipoTexto}</span>
                    <span class="fecha">${marcaje.FEC_MARCAJE}</span>
                    <span class="imagen">
                        <img class="foto_peque" src="./logica/mostrar_imagen.php?archivo=${encodeURIComponent(marcaje.DES_FOTO)}" alt="Foto de fichaje">
                    </span>
                </li>`;
        });
        
        html += `</ul></div>`;
        
        document.getElementById('registro-id-datos').innerHTML = html;
        bloqueMostrarDatos.style.display = 'block';
    });

    document.getElementById('foto_empleado').addEventListener('click', () => {
        document.getElementById('fileinput').click();
    });

    document.getElementById('fileinput').addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const maxSize = 2 * 1024 * 1024;
            if (file.type !== 'image/jpeg') {
                alert('Por favor, selecciona una imagen en formato JPEG.');
                event.target.value = '';
            } else if (file.size > maxSize) {
                alert('El tamaño de la imagen no debe exceder 2 MB.');
                event.target.value = '';
            } else {
                // Crear FormData y añadir la imagen
                const formData = new FormData();
                const cod_empleado = document.getElementById('employee-name').getAttribute('data-id');
                formData.append('imagen', file); 
                formData.append('cod_empleado', cod_empleado);
                
                // Enviar la imagen al servidor
                fetch('logica/subir_foto.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la subida');
                    }
                    return response.text(); // o response.json() si tu PHP devuelve JSON
                })
                .then(data => {
                    //console.log('Imagen subida con éxito:', data);
                        location.reload();                    
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hubo un error al subir la imagen');
                });
            }
        }
    });

   document.getElementById('botonPassword').addEventListener('click', async function(event){
    event.preventDefault();
    document.getElementById("mensajePassword").innerHTML="";    
    const pass = document.getElementById("nuevaPassword").value;
        const pass2 = document.getElementById("nuevaPassword2").value;
        const oldpass = document.getElementById("oldPassword").value;
        if (pass!=pass2) {
            document.getElementById("mensajePassword").innerHTML="Contraseña incorrecta";
            return;

        } else {
            const datos={
                accion:"cambiarPass",
                valor:pass,
                valorViejo: oldpass
            };
            if (await crud(datos)){
                updateContent('perfil');
                document.getElementById("nuevaPassword").value="";
                document.getElementById("nuevaPassword2").value="";
                document.getElementById("oldPassword").value="";
            }
        }
        
    });

    document.getElementById('exp-reg-csv').addEventListener('click', () => {
        //console.log(registros);   
        exportar('csv', registros); // Llama a la función de exportación  
    });
    document.getElementById('exp-reg-xls').addEventListener('click', () => {
        //console.log(registros);   
        exportar('xls', registros); // Llama a la función de exportación  
    });
    document.getElementById('exp-reg-pdf').addEventListener('click', () => {
        const elemento = document.getElementById('registrosExportables');
    
        if (!elemento) {
            console.error('No se encontró el elemento con ID "registrosExportables"');
            return null;
        }
    
        // Clonar el elemento para no afectar el original
        const clon = elemento.cloneNode(true);
    
        
        exportarHTML('pdf', clon.outerHTML); // Llama a la función de exportación  
    });

});

async function exportarHTML(tipo, data) {
    try {
        const formData = new FormData();
        formData.append('datos', data); // Enviar el HTML directamente
        formData.append('tipo', tipo);

        const response = await fetch(`./logica/exportar_registros.php`, {
            method: 'POST',
            body: formData
        });

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `registros_${new Date().toISOString().slice(0,10)}.${tipo}`;
        document.body.appendChild(a);
        a.click();
        a.remove();
    } catch (error) {
        console.error('Error al exportar:', error);
    }
}
//Función exportar registros
async function exportar(tipo,data){
    try {
        // Resto de tu lógica de exportación
        const formData = new FormData();
        formData.append('datos', JSON.stringify(data));
        formData.append('tipo', tipo);

        const response = await fetch(`./logica/exportar_registros.php`, {
          method: 'POST',
          body: formData
        });
        
        // Manejar la descarga
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `registros_${new Date().toISOString().slice(0,10)}.${tipo}`;
        document.body.appendChild(a);
        a.click();
        a.remove();
      } catch (error) {
        console.error('Error al exportar:', error);
    }
}

async function peticionDatos(labels, dato, ausencias, average, maxHorasDia) {
    const cod_empleado = document.getElementById('employee-name').getAttribute('data-id');
    const filterModeSelect = document.getElementById('filter-mode');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const data = {
        accion: 'cargar_grafica',
        empleado: Number(cod_empleado),
        filtro: filterModeSelect.value,
        desdeFecha: startDate.value,
        hastaFecha: endDate.value

    };
    //console.log(data);
    try {
        const response = await fetch('./logica/filtrar_registros.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        return await response.json();
    } catch (error) {
        console.error("Error:", error);
        return null;
    }
}
// Configuración de la gráfica
// Variable global para almacenar la instancia del gráfico
let chartInstance = null;

async function renderChart(labels, data, ausencias, average, maxHorasDia) {
    try {
        const ctx = document.getElementById('hours-chart').getContext('2d');
        
        // Destruir el gráfico anterior si existe
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        const datos = await peticionDatos(labels, data, ausencias, average, maxHorasDia);
        
        // Actualizar variables con los datos recibidos
        labels = datos.labels;
        data = datos.valores;
        ausencias = datos.ausencias;
        average = datos.average;
        maxHorasDia = datos.maxHoras;
        todosMarcajes = datos.datosMarcajes;
        
        //console.log('Datos para gráfica:', datos);

        // Divide las horas en normales y extras con validación
        const horasNormales = data.map(horas => Math.min(Number(horas) || 0, Number(maxHorasDia) || 8));
        const horasExtras = data.map(horas => Math.max(0, (Number(horas) || 0) - (Number(maxHorasDia) || 8)));

        // Define los colores para las barras
        const backgroundColors = labels.map(label => {
            try {
                const date = new Date(label);
                return date.getDay() === 0 ? 'rgba(255, 99, 132, 0.5)' : 'rgba(54, 162, 235, 0.5)';
            } catch (e) {
                return 'rgba(54, 162, 235, 0.5)'; // Color por defecto si hay error con la fecha
            }
        });

        // Crear nueva instancia del gráfico
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Horas normales',
                        data: horasNormales,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Horas extras',
                        data: horasExtras,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Ausencias',
                        data: ausencias,
                        backgroundColor: 'rgba(255, 206, 86, 0.5)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1,
                        type: 'line' // Cambiamos a tipo línea para mejor visualización
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        suggestedMax: (Number(maxHorasDia) || 8) + 2 // Margen adicional
                    }
                },
                plugins: {
                    annotation: {
                        annotations: {
                            line1: {
                                type: 'line',
                                yMin: average,
                                yMax: average,
                                borderColor: 'red',
                                borderWidth: 2,
                                borderDash: [6, 6],
                                label: {
                                    content: `Media: ${average.toFixed(2)}`,
                                    enabled: true,
                                    position: 'end'
                                }
                            },
                            line2: {
                                type: 'line',
                                yMin: maxHorasDia,
                                yMax: maxHorasDia,
                                borderColor: 'green',
                                borderWidth: 2,
                                borderDash: [6, 6],
                                label: {
                                    content: `Máximo: ${maxHorasDia}`,
                                    enabled: true,
                                    position: 'start'
                                }
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw} horas`;
                            }
                        }
                    }
                }
            }
        });

        return chartInstance; // Opcional: devolver la instancia para control externo

    } catch (error) {
        console.error('Error al renderizar gráfica:', error);
        
        // Limpiar instancia en caso de error
        if (chartInstance) {
            try {
                chartInstance.destroy();
            } catch (e) {
                console.error('Error al destruir gráfica previa:', e);
            }
            chartInstance = null;
        }
        
        throw error; // Re-lanzar el error para manejo externo
    }
}

function openTab(evt, tabName) {
    // Oculta todos los contenidos de pestañas
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }
    
    // Elimina la clase active de todos los botones
    const tabButtons = document.getElementsByClassName("tab-button");
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }
    
    // Muestra la pestaña actual y marca el botón como activo
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
}

// Función para renderizar contenido de la sección
function updateContent(seccion) {
    cerrarSecciones();
    document.getElementById(seccion).style.display = "block";
}

function cerrarSecciones(){
    document.getElementById('principal').style.display = "none";
    document.getElementById('perfil').style.display = "none";
    document.getElementById('actividades').style.display = "none";
    document.getElementById('ultimos').style.display = "none";
    document.getElementById('filtrar').style.display = "none";
    document.getElementById('cambiarPassword').style.display = "none";
}
// Función de logout
async function logout() {
    const datos={
        accion:"cerrarSesion"
    };
    if (await crud(datos)){
        window.location.href = './login.php';
    }
}
//función CRUD
async function crud(datos){
    //console.error(datos);
    try {
        const respuesta = await fetch('./logica/filtrar_registros.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datos)
        });
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status} - ${respuesta.statusText}`);
        }
        const resultado = await respuesta.json();
        //console.log(resultado);
        if (resultado.success) {
            if(resultado.mensaje){alert(resultado.mensaje);}
            return true;          
        } else {
            throw new Error(resultado.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert(`Error: ${error.message}`);
        return false;
    }
}

async function cargarHTML(data, formato = 'html') {
    try {
        const response = await fetch('./logica/filtrar_registros.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ...data,
                formato // Añadimos el formato solicitado a los datos
            })
        });
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        if (formato === 'json') {
            return await response.json(); // Devuelve objeto JSON
        } else {
            return await response.text(); // Devuelve HTML como antes
        }
    } catch (error) {
        console.error("Error:", error);
        return formato === 'json' 
            ? { error: true, message: error.message }
            : `<p class="error-message">Error al cargar los datos: ${error.message}</p>`;
    }
}