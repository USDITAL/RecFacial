//Definición de los requisitos.
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { FaceMatcher, LabeledFaceDescriptors } = require('face-api.js');
const axios = require('axios'); // Para hacer solicitudes HTTP
const path = require('path');

const https = require('https'); // Para Node.js

// Configurar axios para ignorar certificados SSL no válidos
const agent = new https.Agent({  
  rejectUnauthorized: false
});

//Definición para el servicio web
const app = express();
const port = 3000;
// Objeto para almacenar claves temporales
const clavesTemporales = {}; 

// Habilitar CORS para todas las rutas, eliminar en producción <<<<<<<< PRODUCCION >>>>>>>>
app.use(cors());
//Necesario para gestionar las solicitudes POST
app.use(bodyParser.json());
//variables para comparar los rostros
let faceMatcher = null;
const descriptoresConocidos = [];

//Función para realizar el marcaje enviando los datos a registrar.php
async function registrar(empleado, cod_bio,cod_tipo,foto,fec,incidencia,pendiente,obs,foto){
    const datos = {
        empleado: empleado, // ID del empleado
        bio: cod_bio, // Código biométrico
        foto: foto, // Foto en formato base64
        tipo_acceso: cod_tipo, // Tipo de acceso
        fec_marcaje: fec, // Fecha de marcaje
        incidencia: incidencia, // Indicador de incidencia
        pendiente: pendiente, // Indicador de pendiente
        obs: obs // Observaciones
    };
    // Enviar los datos con axios
    try {
        const response = await axios.post('https://localhost/Proyecto-DAW/public/logica/registrar.php', datos,{
  httpsAgent: agent 
});
        console.log('Respuesta de PHP:', response.data);
        return response.data; // Devuelve directamente 1, 2 o lo que sea que envíe PHP
    } catch (error) {
        console.error('Error al enviar datos a PHP:', error.message);
        throw error; // Propaga el error para manejarlo en `/fichar`
    }
}

// Función para cargar descriptores desde el archivo PHP
async function cargarDescriptores() {
    try {
        // Limpiar los descriptores existentes
        descriptoresConocidos.length = 0;

        // Hacer una solicitud HTTP a listar_descriptores.PHP
        const response = await axios.get('https://localhost/Proyecto-DAW/public/logica/listar_descriptores.php', {
  httpsAgent: agent 
});
        //Guardamos los datos
        const data = response.data;

        // Verificar que la respuesta sea un array
        if (!Array.isArray(data)) {
            throw new Error('La respuesta no es un array de descriptores.');
        }

        // Procesar los descriptores obtenidos
        data.forEach((item) => {
            //Comprobamos que tengan el formato
            if (item.nombre && item.descriptor && Array.isArray(item.descriptor)) {
                //Añadimos al array los datos
                descriptoresConocidos.push({
                    empleado: item.cod_empleado,
                    cod_tipo: item.cod_tipo,
                    cod_bio: item.cod_bio,
                    nombre: item.nombre,
                    descriptor: new Float32Array(item.descriptor) // Convertir a Float32Array
                });
            } else {
                //Mostramos el error si lo hay
                console.error('Descriptor inválido:', item);
            }
        });
        //Loggeamos el exito
        console.log('Descriptores cargados correctamente desde PHP.');
    } catch (error) {
        //Mostramos el error
        console.error('Error al cargar los descriptores:', error.message);
        throw error; // Propagar el error para manejarlo en la ruta
    }
}

// Ruta del servicio web para reconocer una cara
app.post('/recognize', (req, res) => {
    //Loggeamos la solicitud recibida
    console.log('Solicitud recibida en /recognize');
    //Si no están iniciado los descriptores mostramos error y lo devolvemos
    if (!faceMatcher) {
        console.error('FaceMatcher no inicializado');
        return res.status(500).json({ error: 'FaceMatcher no inicializado' });
    }
    //Creamos un descriptor con los datos recibidos para compararlo
    const descriptor = new Float32Array(req.body.descriptor);
    //Obtenemos el mejor match
    const mejorMatch = faceMatcher.findBestMatch(descriptor);
    //Si está por debajo del umbral se descompone el campo label para obtener datos de
    //identificación. El umbral, cuanto más bajo, más exigente es
    if (mejorMatch.distance < 0.4) { // Ajusta el umbral según sea necesario<<<<<< PARAMETRO >>>>>>>>
        //Descomponemos label
        const [id_empleado, nombre,codtipo,codbio] = mejorMatch.label.split('-');
        // Generar una clave aleatoria
        const clave = Math.random().toString(36).substring(2, 15);

        // Asociar la clave con el ID del empleado para evitar inyección maliciosa del id_empelado
        clavesTemporales[clave] = id_empleado;
        //Devolvemos datos
        res.json({ match: true, cod_bio:codbio,cod_tipo: codtipo,empleado: clave, nombre: nombre, distance: mejorMatch.distance });
    } else {
        //Si no hubo match devolvemos false
        res.json({ match: false });
    }
});

//Ruta del servicio web para recargar descriptores cuando se ha añadido uno nuevo.
app.post('/reload-descriptors', async (req, res) => {
    try {
        // Limpiar los descriptores existentes
        descriptoresConocidos.length = 0;

        // Cargar los nuevos descriptores desde PHP
        await cargarDescriptores();

        // Verificar que hay descriptores cargados
        if (descriptoresConocidos.length === 0) {
            console.error('No se cargaron descriptores.');
            return res.status(500).json({ error: 'No se cargaron descriptores.' });
        }

        // Crear LabeledFaceDescriptors, en el label añadimos el empleado, nombre y tipo
        const labeledDescriptors = descriptoresConocidos.map((item) => (
            new LabeledFaceDescriptors(`${item.empleado}-${item.nombre}-${item.cod_tipo}-${item.cod_bio}`, [item.descriptor])
        ));

        // Actualizar FaceMatcher
        faceMatcher = new FaceMatcher(labeledDescriptors);
        //Loggeamos el exito
        console.log('FaceMatcher actualizado con nuevos descriptores.');
        //Devolvemos mensaje de exito
        res.json({ message: 'Descriptores recargados correctamente.' });
    } catch (error) {
        //En caso de error mostramos error
        console.error('Error al recargar los descriptores:', error.message);
        res.status(500).json({ error: 'Error al recargar los descriptores.' });
        throw error; // Propagar el error para manejarlo en la ruta
    }
});

//servicio web de confirmación de identidad y fichaje
app.post('/fichar', async (req, res) => {
    try{
        //Cargamos los campos del body del POST
        const { id, cod_bio, cod_tipo,fecha, incidencia,pendiente,obs, foto } = req.body; // Recoger el ID del empleado enviado por el cliente
        // Validar la clave obtenida del POST para conocer el empleado correspondiente
        if (!clavesTemporales[id]) {
            //Si no existe lanzamos error
            console.error('Clave inválida o expirada.');
            return res.status(400).json({ error: 'Clave inválida o expirada.' });
        }
        //Si no incluye una fecha la creamos
        if (!fecha){
            fec=new Date().toISOString();
        } else{
            fec=fecha;
        }
        // Recuperar el ID del empleado asociado a la clave
        const id_empleado = clavesTemporales[id];

        // Eliminar la clave para que no pueda reutilizarse
        delete clavesTemporales[id];
        //Hacemos el marcaje en la BBDD
        const respuesta = await registrar(id_empleado,cod_bio,cod_tipo,'',fec,incidencia,pendiente,obs, foto);
        //Loggeamos el fichaje
        console.log(respuesta);
        console.log(`Empleado que fichó: ${id_empleado} con Clave ${id}`); // Mostrar en consola
        //Devolvemos mensaje
        res.json(respuesta);
    }catch (error){
        //Si hay error lo mostramos
        console.error('Error al fichar:', error.message);
        res.status(500).json({ error: 'Error al fichar en el servidor.' });
        throw error; // Propagar el error para manejarlo en la ruta
    }
});


// Iniciar el servicio web para escuchar las peticiones
app.listen(port, "0.0.0.0", async () => {
    //Loggeamos el estado del servidor node.js
    console.log(`Servidor corriendo en http://localhost:${port}`);
   try{
        await cargarDescriptores(); // Cargar descriptores desde PHP

        // Verificar que hay descriptores cargados
        if (descriptoresConocidos.length === 0) {
            console.error('No se cargaron descriptores. FaceMatcher no se inicializará.');
            return;
        }

        // Crear LabeledFaceDescriptors
        const labeledDescriptors = descriptoresConocidos.map((item) => (
            //en la creaciój definimos los datos de label que después deberemos descomponer
            new LabeledFaceDescriptors(`${item.empleado}-${item.nombre}-${item.cod_tipo}-${item.cod_bio}`, [item.descriptor])
        ));

        // Inicializar FaceMatcher
        faceMatcher = new FaceMatcher(labeledDescriptors);
        //Loggeamos el exito al inicializar los descriptores
        console.log('FaceMatcher inicializado con descriptores conocidos.');
    }catch(error){
        console.error('Error al iniciar el servicio:', error.message);
        throw error;
    }
});