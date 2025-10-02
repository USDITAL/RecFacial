
/* Listeners */
document.addEventListener('DOMContentLoaded', () => {
    //Variables
        //Bloques
        const panelDatosAdmin = document.getElementById('panelDatosAdmin');
        const panelIncidencias = document.getElementById('panelIncidencias');
        const panelFichaIncidencia = document.getElementById('panelFichaIncidencia');
        const panelFormularioResolucion = document.getElementById('panelFormularioResolucion');
        const panelEntrasSalidas = document.getElementById('panelEntrasSalidas');
        const panelDatosEmpleado = document.getElementById('panelDatosEmpleado');
        const panelEmpleados = document.getElementById('panelEmpleados');
        const panelConfirmarBaja = document.getElementById('panelConfirmarBaja');
        const panelExportarEmpleados = document.getElementById('panelExportarEmpleados');
        const panelUsuarios = document.getElementById('panelUsuarios');
        const panelDescriptores = document.getElementById('panelDescriptores');
        const panelEliminarDescriptor = document.getElementById('panelEliminarDescriptor');
        const panelExportarUsuarios = document.getElementById('panelExportarUsuarios');
        const panelListadoTransacciones = document.getElementById('panelListadoTransacciones');
        const panelListadoMarcajes = document.getElementById('panelListadoMarcajes');
        const panelRoles = document.getElementById('panelRoles');
        const panelUsuariosAsignados = document.getElementById('panelUsuariosAsignados');
        const panelAsignarRoles = document.getElementById('panelAsignarRoles');
        const panelAjustes = document.getElementById('panelAjustes');
        const panelBienvenida = document.getElementById('panelBienvenida');

    //Aplicar permisos
    aplicarPermisos();
    //Métodos
    function cerrar_bloques() {
        const paneles = [
            'panelDatosAdmin', 'panelIncidencias', 'panelFichaIncidencia',
            'panelFormularioResolucion', 'panelEntrasSalidas', 'panelDatosEmpleado',
            'panelEmpleados', 'panelConfirmarBaja', 'panelExportarEmpleados',
            'panelUsuarios', 'panelDescriptores', 'panelEliminarDescriptor',
            'panelExportarUsuarios', 'panelListadoTransacciones', 'panelListadoMarcajes',
            'panelRoles', 'panelUsuariosAsignados', 'panelAsignarRoles', 'panelAjustes',
            'panelBienvenida'
        ];
    
        paneles.forEach(id => {
            const panel = document.getElementById(id);
            if (panel) { // Solo si el elemento existe
                panel.style.display = 'none';
            }
        });
    }
    //Listeners
    //Listeners del menu
    document.getElementById('menuPrincipal').addEventListener('click', () => {
        cerrar_bloques();
        panelDatosAdmin.style.display = 'block';
        panelIncidencias.style.display = 'block';
        panelEntrasSalidas.style.display = 'block';
    });

    document.getElementById('menuEmpleados').addEventListener('click', () => {
        cerrar_bloques();
        panelEmpleados.style.display = 'block';
        const event = new Event('change');
        document.getElementById('seleccionPanelEmpleado').dispatchEvent(event);
    });

    document.getElementById('menuUsuarios').addEventListener('click', () => {
        cerrar_bloques();
        panelUsuarios.style.display = 'block';
        const event = new Event('change');
        document.getElementById('seleccionPanelUsuario').dispatchEvent(event);
    });

    document.getElementById('menuMarcajes').addEventListener('click', () => {
        cerrar_bloques();
        panelListadoMarcajes.style.display = 'block';
    });  

    document.getElementById('menuTransacciones').addEventListener('click', () => {
        cerrar_bloques();
        panelListadoTransacciones.style.display = 'block';

    });  

    document.getElementById('menuRoles').addEventListener('click', () => {
        cerrar_bloques();
        panelRoles.style.display = 'block';
        const event = new Event('change');
        document.getElementById('seleccionRol').dispatchEvent(event);
    });  

    document.getElementById('menuUsuariosRoles').addEventListener('click', () => {
        cerrar_bloques();
        panelAsignarRoles.style.display = 'block';
        const event = new Event('change');
        document.getElementById('seleccionUsuarioRol').dispatchEvent(event);
    });

    document.getElementById('menuAjustes').addEventListener('click', () => {
        cerrar_bloques();
        panelAjustes.style.display = 'block';
    });  
    document.getElementById('menuPortalEmpleado').addEventListener('click', () => {
        window.location.href = './empleado.php';
    });
    document.getElementById('menuCerrar').addEventListener('click', () => {
        logout();
    }); 
    
    // Evento delegado para todos los elementos .cerrar
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('cerrar') || e.target.closest('.cerrar')) {
            document.querySelectorAll('.ventana').forEach(ventana => {
                ventana.style.display = 'none';
            });
            // También ocultamos el contenedor principal por si acaso
            document.getElementById('panelFichaIncidencia').style.display = 'none';
        }
    });

    //Listeners de incidencias
    //CLICK en una incidencia pendiente
    document.querySelectorAll('.incidenciaP').forEach(incidenciaPendiente => {
        incidenciaPendiente.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const foto = this.getAttribute('data-foto');
            //Devuelvo del array incidenciasP la clickeada 
            const incidenciaSeleccionada = Object.values(incidenciasP).filter(incidencia => {
                return String(incidencia.ID).startsWith(id);
            })[0];
            const fecha=incidenciaSeleccionada.FECHA_INC;
            const empleado = incidenciaSeleccionada.COD_EMPLEADO;

            const comentario = incidenciaSeleccionada.COMENTARIO;
            const formulario = document.getElementById("panelFichaIncidencia");
            const datos = {
                accion: 'mostrar_incidencia',
                cod_empleado: empleado,
                fecha: fecha,
                nombre: nombre,
                comentario: comentario,
                id: id 
            };
    
            // Limpiar formulario
            formulario.innerHTML = "";
                // Cargar el HTML
                cargarHTML(datos)
                .then(html =>{
                formulario.innerHTML = html;
                aplicarPermisos();
            });
            
            // Mostrar la ventana
            document.getElementById('panelFichaIncidencia').style.display = 'block';
        });
    });
    //CLICK en un marcaje de la incidencia pendiente
    document.getElementById('panelFichaIncidencia').addEventListener('click', function(e) {
        // Verifica si el click fue en un elemento con clase marcajeIncidenciaP o en sus hijos
        const elementoMarcaje = e.target.closest('.marcajeIncidenciaP');
        
        if (elementoMarcaje) {
            var codMarcaje = elementoMarcaje.getAttribute('data-id');
            const marcajePorID = Object.values(marcajesPorIncidencia)
                .flat()
                .find(marcaje => marcaje.COD_MARCAJE == codMarcaje);
            
            if (marcajePorID) {
                document.getElementById('panelFormularioResolucion').style.display = 'block';
                document.getElementById('resolucionCod').value=marcajePorID.COD_MARCAJE;
                document.getElementById('resolucionEmpleado').value=marcajePorID.COD_EMPLEADO;
                document.getElementById('resolucionFecha').value=marcajePorID.FEC_MARCAJE;
            }
        }
    });

    //CLICK para Actualizar marcaje de incidencia
    document.getElementById('resolucionG').addEventListener('click', async () => {
        // Envío de datos para actualizar marcaje
        await crud({
            cod_marcaje: document.getElementById('resolucionCod').value,
            cod_empleado: document.getElementById('resolucionEmpleado').value,
            fec_marcaje: document.getElementById('resolucionFecha').value,
            cod_incidencia: Number(document.getElementById('incidenciaActiva').getAttribute("data-incidencia")),
            cod_usuario: usuarioSesion,
            accion: 'actualizar_marcaje_incidencia'
        });
        location.reload();
    });

    //CHANGE en el select del formularioEmpleados
    document.getElementById("seleccionPanelEmpleado").addEventListener("change", function() {
        const formulario = document.getElementById("formularioEmpleado");
        const valorSeleccionado = this.value; // Obtiene el valor del select
        const data = {
            accion: 'mostrar_empleado',
            cod_empleado: valorSeleccionado  // Parámetro adicional opcional
        };
    
        // Limpiar formulario
        formulario.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            formulario.innerHTML = html;
            aplicarPermisos();
        });

        const formulario2 = document.getElementById("exportarEmpleado");
            formulario2.style.display = 'none';
            const data2 = {
                accion: 'exportar_empleado',
                cod_empleado: valorSeleccionado  // Parámetro adicional opcional
            };
        
            // Limpiar formulario
            formulario2.innerHTML = "";
            // Cargar el HTML
            cargarHTML(data2)
            .then(html =>{
                formulario2.innerHTML = html;
                aplicarPermisos();
        });
    });

    //CHANGE en el select del formularioUsuarios
    document.getElementById("seleccionPanelUsuario").addEventListener("change", function() {
        const formulario = document.getElementById("formularioUsuario");
        const valorSeleccionado = this.value; // Obtiene el valor del select
        const data = {
            accion: 'mostrar_usuario',
            cod_usuario: valorSeleccionado  // Parámetro adicional opcional
        };
    
        // Limpiar formulario
        formulario.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            formulario.innerHTML = html;
            aplicarPermisos();
        });

            const formulario2 = document.getElementById("exportarUsuario");
            formulario2.style.display = 'none';
            const data2 = {
                accion: 'exportar_usuario',
                cod_usuario: valorSeleccionado  // Parámetro adicional opcional
            };
        
            // Limpiar formulario
            formulario2.innerHTML = "";
            // Cargar el HTML
            cargarHTML(data2)
            .then(html =>{
                formulario2.innerHTML = html;
                aplicarPermisos();
        });
    });

    //CLICK en filtrar Transacciones
    document.getElementById("filtrarTransacciones").addEventListener("click", function() {
        //Elementos de la página
        const listado = document.getElementById("listaTransacciones");
        const listado2 = document.getElementById("exportarTransaccion");
        const desdeFecha = document.getElementById("fechaInicioTrans");
        const hastaFecha = document.getElementById("fechaFinTrans");
        const desdeUsuario = document.getElementById("usuarioInicioTrans");
        const hastaUsuario = document.getElementById("usuarioFinTrans");
        const desdeActividad = document.getElementById("actividadInicioTrans");
        const hastaActividad = document.getElementById("actividadFinTrans");
        const data = {
            accion: 'mostrar_transacciones',
            desdeFecha: desdeFecha.value.toString(),
            hastaFecha: hastaFecha.value.toString(),
            desdeUsuario: desdeUsuario.value,
            hastaUsuario: hastaUsuario.value,
            desdeActividad: desdeActividad.value,
            hastaActividad: hastaActividad.value,
        };
        
        // Limpiar formulario
        listado.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            listado.innerHTML = html;
            aplicarPermisos();
        });

        const data2 = {
            accion: 'exportar_transacciones',
            desdeFecha: desdeFecha.value.toString(),
            hastaFecha: hastaFecha.value.toString(),
            desdeUsuario: desdeUsuario.value,
            hastaUsuario: hastaUsuario.value,
            desdeActividad: desdeActividad.value,
            hastaActividad: hastaActividad.value,
        };
        
        // Limpiar formulario
        listado2.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data2)
        .then(html =>{
            listado2.innerHTML = html;
            aplicarPermisos();
        });
    });

    //CLICK en filtrar Marcajes
    document.getElementById("filtrarMarcajes").addEventListener("click", function() {
        //Elementos de la página
        const listado = document.getElementById("listaMarcajes");
        const listado2 = document.getElementById("exportarMarcaje");
        const desdeFecha = document.getElementById("fechaInicioMarcaje");
        const hastaFecha = document.getElementById("fechaFinMarcaje");
        const desdeEmpleado = document.getElementById("empleadoInicioMarcaje");
        const hastaEmpleado = document.getElementById("empleadoFinMarcaje");
        const desdeTipo = document.getElementById("tipoInicioMarcaje");
        const hastaTipo = document.getElementById("tipoFinMarcaje");
        const data = {
            accion: 'mostrar_marcajes',
            desdeFecha: desdeFecha.value.toString(),
            hastaFecha: hastaFecha.value.toString(),
            desdeEmpleado: desdeEmpleado.value,
            hastaEmpleado: hastaEmpleado.value,
            desdeTipo: desdeTipo.value,
            hastaTipo: hastaTipo.value,
        };
        
        // Limpiar formulario
        listado.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            listado.innerHTML = html;
            aplicarPermisos();
        });

        const data2 = {
            accion: 'exportar_marcajes',
            desdeFecha: desdeFecha.value.toString(),
            hastaFecha: hastaFecha.value.toString(),
            desdeEmpleado: desdeEmpleado.value,
            hastaEmpleado: hastaEmpleado.value,
            desdeTipo: desdeTipo.value,
            hastaTipo: hastaTipo.value,
        };
        
        // Limpiar formulario
        listado2.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data2)
        .then(html =>{
            listado2.innerHTML = html;
            aplicarPermisos();
        });
    });

    //CHANGE en el select de Roles
    document.getElementById("seleccionRol").addEventListener("change", function() {
        const formulario = document.getElementById("datosRol");
        const valorSeleccionado = this.value; // Obtiene el valor del select
        const data = {
            accion: 'mostrar_rol',
            cod_rol: valorSeleccionado 
        };
    
        // Limpiar formulario
        formulario.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            formulario.innerHTML = html;
            aplicarPermisos();
        });
    });

    //CHANGE en el select de usuarioRoles
    document.getElementById("seleccionUsuarioRol").addEventListener("change", function() {
        const formulario = document.getElementById("datosUsuarioRol");
        const valorSeleccionado = this.value; // Obtiene el valor del select
        const data = {
            accion: 'mostrar_usuariorol',
            cod_usuario: valorSeleccionado 
        };
    
        // Limpiar formulario
        formulario.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            formulario.innerHTML = html;
            aplicarPermisos();
        });
    });

    //CLICK en menú ajustes
    document.getElementById("menuAjustes").addEventListener("click", function() {
        const formulario = document.getElementById("panelAjustes");
        const data = {
            accion: 'mostrar_ajustes'
        };
    
        // Limpiar formulario
        formulario.innerHTML = "";
        // Cargar el HTML
        cargarHTML(data)
        .then(html =>{
            formulario.innerHTML = html;
            aplicarPermisos();
        });
    });


    //Mover roles CRUD
    document.getElementById('panelAsignarRoles').addEventListener('click', async function(e) {
        // Busca el elemento .linea_roles que tenga la clase rolesPosibles
        const rolPosible = e.target.closest('.linea_roles.rolesPosibles');
        const rolAsignado = e.target.closest('.linea_roles.rolesAsignados');
        const formulario = document.getElementById("datosUsuarioRol");
        
        if (rolPosible) {
            var codRol = Number(rolPosible.getAttribute('data-id'));
            var usuario = Number(rolPosible.getAttribute('data-usuario'));
            var nombreRol = rolPosible.querySelector('.form-label').textContent;
            datos={
                accion: 'asigna_rol',
                cod_usuario: usuario,
                cod_rol: codRol
            };
            await crud(datos);
            const data = {
                accion: 'mostrar_usuariorol',
                cod_usuario: usuario 
            };
        
            // Limpiar formulario
            formulario.innerHTML = "";
            // Cargar el HTML
            await cargarHTML(data)
            .then(html =>{
                formulario.innerHTML = html;
                aplicarPermisos();
            });
        }

        if (rolAsignado) {
            var codRol = Number(rolAsignado.getAttribute('data-id'));
            var usuario = Number(rolAsignado.getAttribute('data-usuario'));
            var nombreRol = rolAsignado.querySelector('.form-label').textContent;
            datos={
                accion: 'quita_rol',
                cod_usuario: usuario,
                cod_rol: codRol
            };
            await crud(datos);
            const data = {
                accion: 'mostrar_usuariorol',
                cod_usuario: usuario 
            };
        
            // Limpiar formulario
            formulario.innerHTML = "";
            // Cargar el HTML
            await cargarHTML(data)
            .then(html =>{
                formulario.innerHTML = html;
            });
        }
    });

    //Asignar Ajustes CRUD
    document.getElementById('panelAjustes').addEventListener('change', async function(e) {
        // Busca el elemento .linea_roles que tenga la clase rolesPosibles
        const lineaAjuste = e.target.closest('.elementoAjuste');
        const valor = e.target.value;

        const datos={
            accion: 'guarda_ajuste',
            cod_ajuste: lineaAjuste.getAttribute('data-id'),
            valor: e.target.value
        };
        await crud(datos);
                
    });

    //CLICK en guardar rol
    document.getElementById('panelRoles').addEventListener('click', async function(e) {
        if (e.target && e.target.id === 'guardarRol') {
            const checkboxes = document.querySelectorAll('.rolCheckbox');
            const privilegios = {};
            checkboxes.forEach(checkbox => {
            const clave = checkbox.getAttribute('data-id');
            privilegios[clave] = checkbox.checked ? true : false;
            });
            codRol = document.getElementById('campoCodRol').value>"" ? Number(document.getElementById('campoCodRol').value) : 0;
            
            const datos = {
                accion: 'guarda_rol',
                privilegios: privilegios,
                cod_rol: codRol,
                nom_rol: document.getElementById('campoNomRol').value,
                des_rol: document.getElementById('campoDesRol').value

            };
            await crud(datos);
            if (codRol==0){location.reload();}
        }
        if (e.target && e.target.id === 'bajaRol') {
            const respuesta = await mensajeConfirmacion("¿Estás seguro de dar de baja este rol?");
            if (respuesta) {
                const datos = {
                    accion: 'baja_rol',
                    cod_rol: document.getElementById('campoCodRol').value
                };
                await crud(datos);
                const event = new Event('click');
                document.getElementById('panelRoles').dispatchEvent(event);
            } else {
                
            }
            
        }

        if (e.target && e.target.id === 'nuevoRol') {
            const formulario = document.getElementById("datosRol");
            const valorSeleccionado = this.value; // Obtiene el valor del select
            const data = {
                accion: 'mostrar_nuevo_rol',
                cod_rol: valorSeleccionado 
            };
    
            // Limpiar formulario
            formulario.innerHTML = "";
            // Cargar el HTML
            await cargarHTML(data)
            .then(html =>{
                formulario.innerHTML = html;
                aplicarPermisos();
            });
            
        }
    });

    //CLICK en nuevo usuario
    document.getElementById('nuevoUsuario').addEventListener('click', async function() {
        
        document.getElementById('seleccionPanelUsuario').value=0;
        const formulario = document.getElementById("formularioUsuario");
        const data = {
            accion: 'mostrar_nuevo_usuario'
        };

        // Limpiar formulario
        formulario.innerHTML = "";
        // Cargar el HTML
        await cargarHTML(data)
        .then(html =>{
            formulario.innerHTML = html;
            aplicarPermisos();
        });  
    
    });

    //CLICK en grabar y baja usuario
    document.getElementById('formularioUsuario').addEventListener('click', async function(e) {
        if (e.target && e.target.id === 'guardarUsuario') {
            const cod_usuario = document.getElementById('codigoUsuarioUsuario').value>""? Number(document.getElementById('codigoUsuarioUsuario').value) : 0;
            const login = document.getElementById('loginUsuario').value;
            const email = document.getElementById('emailUsuario').value;
            
            const datos = {
                accion: 'graba_usuario',
                cod_usuario: cod_usuario,
                login: login,
                email: email
            };
            
            await crud(datos);
            if (cod_usuario==0){location.reload();
                const event = new Event('click');
                document.getElementById('panelUsuario').dispatchEvent(event);
            }
        }

        if (e.target && e.target.id === 'bajaUsuario') {
            const cod_usuario = document.getElementById('seleccionPanelUsuario').value>""? Number(document.getElementById('seleccionPanelUsuario').value) : 0;
            const respuesta = await mensajeConfirmacion("¿Estás seguro de dar de baja este usuario?");
            if (respuesta) {
                const datos = {
                    accion: 'baja_usuario',
                    cod_usuario: cod_usuario
                };
                await crud(datos);
                const event = new Event('click');
                document.getElementById('panelUsuarios').dispatchEvent(event);
            } else {
                
            }
            
        }

        if (e.target && e.target.id === 'passUsuario') {
        const boton = e.target;
        const cod_usuario = document.getElementById('seleccionPanelUsuario').value>""? Number(document.getElementById('seleccionPanelUsuario').value) : 0;
        if (cod_usuario>0){
            const datos ={
                accion:'pass_usuario',
                cod_usuario: cod_usuario
            };
            boton.textContent = "Generando...";
            boton.disabled = true;

            try {
                await crud(datos); // Esperamos a que termine la operación
            } catch (error) {
                console.error("Error en crud():", error);
            } finally {
                // Restauramos el texto y la funcionalidad (haya éxito o error)
                boton.textContent = "Generar Password";
                boton.disabled = false;
            }
        }
               
        }

        
    });

    //CLICK en nuevo empleado
    document.getElementById('nuevoEmpleado').addEventListener('click', async function() {
        
            document.getElementById('seleccionPanelEmpleado').value=0;
            const formulario = document.getElementById("formularioEmpleado");
            const data = {
                accion: 'mostrar_nuevo_empleado'
            };
    
            // Limpiar formulario
            formulario.innerHTML = "";
            // Cargar el HTML
            await cargarHTML(data)
            .then(html =>{
                formulario.innerHTML = html;
                aplicarPermisos();
            });  
    });
    //CLICK en listaDescriptores
    document.getElementById('guardarDescriptor').addEventListener('click', async function(e) {
        const boton=document.getElementById('guardarDescriptor');
        const selectElement = document.getElementById('seleccionPanelEmpleado');
        const nombreCompleto = selectElement.options[selectElement.selectedIndex].text;
        const empleado = Number(document.getElementById('seleccionPanelEmpleado').value);
        document.getElementById('status').textContent  = "Analizando rostro...";
        boton.textContent = "Guardando...";
        boton.disabled=true;
        try{
            await guardarRostro(nombreCompleto, empleado);
        } catch (error) {
            console.error("Error guardando descriptor:", error);
        } finally{
            boton.textContent = "Guardar Rostro";
            boton.disabled=false;
            await mensajeInformacion("Datos actualizados correctamente.");
        }
        
        document.querySelectorAll('.ventana').forEach(ventana => {
            ventana.style.display = 'none';
            document.getElementById('status').textContent  = "";
        });
    });
    //CLICK en listaDescriptores
    document.getElementById('panelDescriptores').addEventListener('click', async function(e) {
    if (e.target && e.target.id === 'nuevoDescriptor') {
            const formulario = document.getElementById("panelDescriptores");
            document.getElementById('panelCamara').style.display = 'block';
            cargar();

        }
        const lineaBioElement = e.target.closest('.linea_bio');
        if (lineaBioElement) {
            lineaBioElement.classList.toggle('selected');
        }
        if (e.target && e.target.id === 'eliminarDescriptor' && document.querySelectorAll('.linea_bio.selected').length > 0) {
            const respuesta = await mensajeConfirmacion("¿Estás seguro de eliminar los datos biométricos seleccionados?");
            if (respuesta) {
                // Corrección: Usar forEach desde la NodeList devuelta por querySelectorAll
                document.querySelectorAll('.linea_bio.selected').forEach(async function(element) {
                    const codBio = element.getAttribute('data-id');
                    const datos = {
                        accion: 'baja_bio',
                        cod_bio: codBio
                    };
                    await crud(datos);
                });
                document.querySelectorAll('.ventana').forEach(ventana => {
                    ventana.style.display = 'none';
                });
            }
        }
    });

    //CLICK en grabar y baja empleado
    document.getElementById('formularioEmpleado').addEventListener('click', async function(e) {
        if (e.target && e.target.id === 'recalcularBolsa') {
            const campo = document.getElementById("bolsaEmpleado");
            const valorSeleccionado = document.getElementById("seleccionPanelEmpleado").value; // Obtiene el valor del select
            const horas = document.getElementById("horasEmpleado").value;
            const data = {
                accion: 'recalcular_bolsa',
                cod_empleado: valorSeleccionado,  // Parámetro adicional opcional
                horas: horas
            };
            // Cargar el HTML
            cargarHTML(data)
            .then(valor =>{
                campo.value = valor;
                //aplicarPermisos();
            });
            
        }

        if (e.target && e.target.id === 'bioEmpleado') {
            const formulario = document.getElementById('listaDescriptores');
            const cod_empleado = document.getElementById('seleccionPanelEmpleado').value>""? Number(document.getElementById('seleccionPanelEmpleado').value) : 0;
            const datos ={
                accion:'muestra_bio_empleado',
                cod_empleado: cod_empleado
            }
            // Limpiar formulario
            formulario.innerHTML = "";
            // Cargar el HTML
            await cargarHTML(datos)
            .then(html =>{
                formulario.innerHTML = html;
                document.getElementById('panelDescriptores').style.display = 'block';
                aplicarPermisos();
            });
        }
        
        if (e.target && e.target.id === 'guardarEmpleado') {
            const cod_empleado = document.getElementById('seleccionPanelEmpleado').value>""? Number(document.getElementById('seleccionPanelEmpleado').value) : 0;
            const apellido1 = document.getElementById('apellido1Empleado').value;
            const apellido2 = document.getElementById('apellido2Empleado').value;
            const nombre = document.getElementById('nombreEmpleado').value;
            const contacto = document.getElementById('contactoEmpleado').value;
            const usuario = document.getElementById('usuarioEmpleado').value;
            const horario = document.getElementById('horarioEmpleado').value;
            const horas = document.getElementById('horasEmpleado').value;
            const foto = document.getElementById('fotoEmpleado').getAttribute("data-foto");

            const datos = {
                accion: 'graba_empleado',
                cod_empleado: cod_empleado,
                apellido1: apellido1,
                apellido2: apellido2,
                nombre: nombre,
                contacto: contacto,
                usuario: Number(usuario),
                horario: horario,
                horas: Number(horas),
                foto: foto
            };
            
            await crud(datos);
            if (cod_empleado==0){location.reload();
                const event = new Event('click');
                document.getElementById('panelEmpleados').dispatchEvent(event);
            }
        }

        if (e.target && e.target.id === 'bajaEmpleado') {
            const cod_empleado = document.getElementById('seleccionPanelEmpleado').value>""? Number(document.getElementById('seleccionPanelEmpleado').value) : 0;
            const respuesta = await mensajeConfirmacion("¿Estás seguro de dar de baja este empleado?");
            if (respuesta) {
                const datos = {
                    accion: 'baja_empleado',
                    cod_empleado: cod_empleado
                };
                await crud(datos);
                const event = new Event('click');
                document.getElementById('panelEmpleados').dispatchEvent(event);
            } else {
                
            }
            
        }

        
    });

    document.getElementById('exportarUsuarios').addEventListener('click', async function() {
        const elemento = document.getElementById('exportarUsuario');
        elemento.style.display = 'block';

    
        if (!elemento) {
            console.error('No se encontró el elemento con ID "exportarUsuario"');
            return null;
        }
    
        // Clonar el elemento para no afectar el original
        const clon = elemento.cloneNode(true);
        elemento.style.display = 'none';
        
        exportarHTML('pdf', clon.outerHTML); // Llama a la función de exportación  
    });

    document.getElementById('exportarEmpleados').addEventListener('click', async function() {
        const elemento = document.getElementById('exportarEmpleado');
        elemento.style.display = 'block';

    
        if (!elemento) {
            console.error('No se encontró el elemento con ID "exportarEmpleado"');
            return null;
        }
    
        // Clonar el elemento para no afectar el original
        const clon = elemento.cloneNode(true);
        elemento.style.display = 'none';
        
        exportarHTML('pdf', clon.outerHTML); // Llama a la función de exportación  
    });

    document.getElementById('exportarMarcajes').addEventListener('click', async function() {
    const elemento = document.getElementById('listaMarcajes');
    
    if (!elemento) {
        console.error('No se encontró el elemento con ID "exportarMarcaje"');
        return;
    }
    const htmlContent = elemento.outerHTML; // Capturar el HTML completo del contenedor
    exportarHTML('pdf', htmlContent); // Llama a la nueva función de exportación  
});

    document.getElementById('exportarTransacciones').addEventListener('click', async function() {
        const elemento = document.getElementById('exportarTransaccion');
        elemento.style.display = 'block';

    
        if (!elemento) {
            console.error('No se encontró el elemento con ID "exportarTransaccion"');
            return null;
        }
    
        // Clonar el elemento para no afectar el original
        const clon = elemento.cloneNode(true);
        elemento.style.display = 'none';
        
        exportarHTML('pdf', clon.outerHTML); // Llama a la función de exportación  
    });
});

//Función cerrar sesión
async function logout() {
    const datos={
        accion:"cerrarSesion"
    };
    if (await peticionWeb(datos)){
        window.location.href = './login.php';
    }
}

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


async function cargarHTML(data){
    try {
        const response = await fetch('./logica/administracion_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return await response.text();
    } catch (error) {
        console.error("Error:", error);
        return `<p class="error-message">Error al cargar los datos: ${error.message}</p>`;
    }
}

async function crud(datos){
    try {
        const respuesta = await fetch('./logica/administracion_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datos)
        });
        const resultado = await respuesta.json();
        
        if (resultado.success) {
            if(!resultado.mensaje) {
                await mensajeInformacion("Datos actualizados correctamente.");           
            }
            else{
                await mensajeInformacion(resultado.mensaje);
            }
        } else {
            throw new Error(resultado.error || 'Error desconocido');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(`Error al guardar: ${error.message}`);
    }
}

async function peticionWeb(datos){
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

function openTab(tabId) {
    // Oculta todos los contenidos
    document.querySelectorAll('.tab-content').forEach(tab => {
      tab.classList.remove('active');
    });
    // Desactiva todos los tabs
    document.querySelectorAll('.tab').forEach(tab => {
      tab.classList.remove('active');
    });
    // Activa el tab seleccionado
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
  }


function seleccionarEmpleado(elemento) {
    const codEmpleado = elemento.getAttribute('data-cod');
    document.getElementById('cod_empleado').value = codEmpleado;
    document.getElementById('dropdownEmpleados2').textContent = elemento.textContent.trim();
}


// Función para actualizar la hora en vivo
function updateTime() {
    const now = new Date();
    const reloj = document.getElementById('current-time');
    if (!reloj) {
        return; // Si el elemento no existe, no hacemos nada
    }
    reloj.innerText = now.toLocaleTimeString();
}
setInterval(updateTime, 1000);
updateTime();

// Función para cargar el gráfico de asistencia
function loadChart() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'],
            datasets: [{
                label: 'Horas trabajadas',
                data: [8, 7.5, 8, 6, 7],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}



function mensajeConfirmacion(mensaje) {
    return new Promise((resolve) => {
        const modal = document.getElementById('ventana_emergente');
        const messageElement = document.getElementById('mensaje_confirmacion');
        const btnYes = document.getElementById('botonSI');
        const btnNo = document.getElementById('botonNO');

        // Mostrar el mensaje
        messageElement.textContent = mensaje;
        modal.style.display = 'flex';

        // Botón "Sí"
        btnYes.onclick = () => {
            modal.style.display = 'none';
            resolve(true);
        };

        // Botón "No"
        btnNo.onclick = () => {
            modal.style.display = 'none';
            resolve(false);
        };
    });
}

function mensajeInformacion(mensaje) {
    return new Promise((resolve) => {
        const modal = document.getElementById('ventana_emergente_mensaje');
        const messageElement = document.getElementById('mensaje_info');
        const btnAceptar = document.getElementById('botonACEPTAR');

        // Mostrar el mensaje
        messageElement.textContent = mensaje;
        modal.style.display = 'flex';

        btnAceptar.onclick = () => {
            modal.style.display = 'none';
            resolve(true);
        };
    });
}

function aplicarPermisos() {
    // Permisos
    const elementos = {
        'empCrear': 'nuevoEmpleado',
        'empModificar': 'botoneraEmpleado',
        'empBaja': 'bajaEmpleado',
        'usrCrear': 'nuevoUsuario',
        'usrModificar': 'botoneraUsuario',
        'usrBaja': 'bajaUsuario',
        'usrGenerarPass': 'passUsuario',
        //'marCrearPropio': 'nuevoEmpleado',
        //'marConsultarPropio': 'nuevoEmpleado',
        //'marCrear': 'nuevoEmpleado',
        //'marModificar': 'nuevoEmpleado',
        //'marEliminar': 'nuevoEmpleado',
        'marConsultar': 'menuMarcajes',
        'marAuth': 'resolucionG',
        'bioCrear': 'nuevoDescriptor',
        'bioEliminar': 'eliminarDescriptor',
        'rolCrear': 'nuevoRol',
        'rolModificar': 'botoneraRol',
        'rolEliminar': 'bajaRol',
        'ajustesModificar': 'menuAjustes'
    };

    for (const [permiso, id] of Object.entries(elementos)) {
        if (!permisos[permiso]) {
            const elemento = document.getElementById(id);
            if (elemento) { // Solo si el elemento existe
                elemento.style.display = 'none';
            } else {
                console.warn(`Elemento con ID "${id}" no encontrado en el DOM.`);
            }
        }
    }
}