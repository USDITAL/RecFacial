//Declaración de variables de elementos HTML
const video = document.getElementById('video');
const estado = document.getElementById('status');
const botonera = document.getElementById('botonera');
const fileInput = document.getElementById('fileInput');
//Variables para el reconocimiento
let faceMatcher = null;
const descriptoresConocidos = []; // Almacena los descriptores conocidos
const UMBRAL_SIMILITUD = 0.6; // Umbral de similitud (ajusta según sea necesario)
let intervaloAnalisis; // Intervalo de análisis del video
//Variables para parametrizar el último reconocimiento.
let ultimoID="";
let ultimoCodTipo="";
let ultimoCodBio="";

//Promesa de carga de modelos, hasta que no lo estén no se ejecuta el código
//Necesario para dar tiempo a cargar los modelos de reconocimiento

function cargar(){
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('../js/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('../js/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('../js/models'),
        faceapi.nets.faceExpressionNet.loadFromUri('../js/models')
        ]).then(() => {
        //Cuando están inicia el vídeo.
        iniciarVideo();
    });
}

//Inicia la webcam si está disponible o muestra error en estado
function iniciarVideo() {
    //Comprueba que existe el dispositivo
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        //Intenta acceder a él
        navigator.mediaDevices.getUserMedia({ video: true })
           //Si lo consigue añade a video las propiedades y muestra mensaje
            .then(function (stream) {
                video.srcObject = stream;
                video.style.display = 'block';
                console.log("Video cargado.");
                estado.innerHTML = 'Video iniciado';//Cambiar a id de mensajes
            })
            //Si no muestra error
            .catch(function (error) {
                console.error("No se puede acceder a la cámara: ", error);
                estado.innerHTML = 'Error al acceder a la cámara';//Cambiar a id de mensajes
            });
    } else {
        //Si no hay cámara muestra mensaje
        estado.innerHTML = 'La cámara no es compatible con tu navegador';//Cambiar a id de mensajes
    }
}

//Actualiza el reloj en la página
function updateClock() {
    const clockElement = document.getElementById("clock");
    
    // Verificamos si el elemento existe antes de continuar
    if (!clockElement) {
        return; // Salimos de la función si no existe el elemento
    }
    
    let now = new Date();
    let hours = now.getHours().toString().padStart(2, "0");
    let minutes = now.getMinutes().toString().padStart(2, "0");
    let seconds = now.getSeconds().toString().padStart(2, "0");
    
    clockElement.textContent = `${hours}:${minutes}:${seconds}`;
}

//Guarda el rostro detectado en la base de datos
async function guardarRostro(nombre="",empleado) {
    //Captura el rostro destectado
    
    const rostro = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
    if (rostro) {
        //si lo hay pide un nombre y guarda en el servidor y actualiza el detector de rostros.
        const descriptor = rostro.descriptor;
        
        if (nombre=="") {nombre = prompt("Introduce un nombre para este rostro:");}
        if (nombre) {
            //Mandamos el descriptor empleado y usuario que realiza el alta
            //Modificar cuando haya <<<<<<<<<<< BACK-END >>>>>>>>>>>>>>>>
            await guardarDescriptorEnServidor(nombre, descriptor,empleado,'Admon');
            //actualizarFaceMatcher();
        }
    }
}

//Llama al PHP que realiza los cambios en el servidor <<<<meter parámetros desde administración.php>>>
async function guardarDescriptorEnServidor(nombre, descriptor,empleado,usuario) {
    //Agrupamos los datos a enviar
    const data = {nombre, descriptor: Array.from(descriptor),empleado,usuario };//Aquí se deben introducir el cod_Empleado y el usuario_Alta
    try {
        //Se manda por POST los datos al servidor definiendo en response el envío y los datos.
        const response = await fetch('./logica/guardar_descriptor.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        //Se espera la respuesta y se almacena en result
        const result = await response.json();
        //Mostramos en consola el resultado.
        //console.log(result.message);
    } catch (error) {
        //Si ocurre un error mostramos el error.
        console.error('Error al guardar el descriptor:', error);
    }
    //Tras guardar el nuevo descriptor volvemos a cargarlos en server.js
    recargarDescriptores();
}

// Agregar un event listener al div con id "botonera"
if (botonera) {
    botonera.addEventListener("click", function(event) {
        //Evento para reconocer al empleado identificado y fichar
        if (event.target && event.target.id === "reconocido") {
            detenerAnalisis();
            fichar(ultimoID);
        //Evento para indicar que se ha equivocado.
        } else if (event.target && event.target.id === "noReconocido") {
            analizaVideo(); //analiza nuevamente o envía error a administración.
        //Evento para iniciar el reconocimiento
        } else if (event.target && event.target.id === "startRecognition") {
            analizaVideo();
        }
    });
}

//Función que analiza el video en busca de rostros
function analizaVideo() {
    //Crea el canvas utilizando el objeto video.
    const canvas = faceapi.createCanvasFromMedia(video);
    canvas.id = 'overlay';
    document.querySelector('.camera-container').append(canvas);
    const dimensiones = {
        width: video.width,
        height: video.height
    };
    faceapi.matchDimensions(canvas, dimensiones);
    //Espacio la botonera un salto, modificar si es necesario.
    botonera.innerHTML=`<BR>`;
    //Contador de detecciones para parar el proceso.
    let detecciones=0;
    //Bucle de detección de caras. el tiempo entre intentos está definido al final de la función.
    intervaloAnalisis = setInterval(async () => {
        const rostros = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptors().withFaceExpressions();
        const area = faceapi.resizeResults(rostros, dimensiones);
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
        
        //código para mostrar los datos de detección.
        //faceapi.draw.drawDetections(canvas, area);
        //faceapi.draw.drawFaceLandmarks(canvas, area);
        //faceapi.draw.drawFaceExpressions(canvas, area);
        
        //Bucle de rostros 
        rostros.forEach(async (rostro) => {
            const descriptor = rostro.descriptor;
            const descriptorArray = Array.from(descriptor); // Convertir a array para enviar
            //al detectar rostro suma una detección
            detecciones++;
            //si excedemos el número de detecciones paramos. Definir <<<<<<<<PARAMETRO>>>>>>
            if (detecciones>30){
                detenerAnalisis();
            }
            // Enviar el descriptor al servidor Node.js
            try {
                const response = await fetch('https://recfacialch.myddns.me/api/recognize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ descriptor: descriptorArray })
                });
                //Esperamos respuesta del servidor
                const result = await response.json();
                //Si devuelve match mostramos mensaje y cargamos datos.
                if (result.match && result.nombre) {
                    //console.log(result);
                    estado.innerHTML = `Empleado: ${result.nombre}, ID:Reconocimiento:${result.empleado}`; //Distancia: ${result.distance}
                    ultimoID = `${result.empleado}`;
                    ultimoCodBio = `${result.cod_bio}`;
                    ultimoCodTipo = `${result.cod_tipo}`;
                    //Cambia la botonera.
                    botonera.innerHTML = `<button class="btnVerde" id="reconocido">Soy ${result.nombre}</button>
                                        <button class="btnRojo" id="noReconocido">Soy otra persona</button>`;
                    //Limpia el intervalo.
                    clearInterval(intervaloAnalisis);
                }
            } catch (error) {
                //Mostramos error si falla el envío del descriptor
                console.error('Error al enviar el descriptor:', error);
            }
        });
// Definir el intervalo mediante parámetro.<<<<<<<<<<<<<<<PARAMETROS>>>>>>>>>>>>>>
    }, 100);
}

//Función para detener el análisis del video
function detenerAnalisis() {
    //Limpiamos datos
    clearInterval(intervaloAnalisis);
    //Dejamos mensaje
    console.log('Análisis de rostros detenido.');
     // Limpia el canvas
     const canvas = document.getElementById('overlay');
     if (canvas) {
         canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
         canvas.remove(); // Opcional: Elimina el canvas del DOM
     }
    //Actualiza el contenido de botonera
     botonera.innerHTML= `<button class="btnAzul" id="startRecognition">Iniciar Reconocimiento Facial</button>`;
     
}
//Función para volver a descargar descriptores de la BBDD
async function recargarDescriptores() {
    //Se llama al servicio de node.js sin datos adicionales
    try {
        const response = await fetch('https://recfacialch.myddns.me/api/reload-descriptors', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        //Se espera la respuesta
        const result = await response.json();
        //console.log(result.message); // Mostrar el mensaje del servidor
        
    } catch (error) {
        //Mostramos el error
        console.error('Error al recargar los descriptores:', error);
    }
}
//Función para fichar con el idD reconocido.
async function fichar(idD){
    //Bloque try-catch
    try {
        // Crear un canvas para capturar la imagen
        const canvas = document.createElement('canvas');
        const scaleFactor = 0.5; // Escalar al 50% del tamaño original para reducir el peso
        canvas.width = video.videoWidth * scaleFactor;
        canvas.height = video.videoHeight * scaleFactor;
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convertir la imagen a Base64
        const fotoBase64 = canvas.toDataURL('image/jpeg', 0.7); // Comprensión al 70%

        
        
        // Crear el objeto de datos a enviar. Los datos de incidencia y obs solo son necesarios
        //En fichajes manuales que se harán desde el back-end
        const datos ={
            id: idD,
            cod_bio: ultimoCodBio,
            cod_tipo: ultimoCodTipo,
            fecha: new Date().toISOString(),
            incidencia:0,
            pendiente:0,
            obs: "",
            foto: fotoBase64 // Incluir la imagen capturada
        };
        // Enviar los datos al servidor
        const response = await fetch('https://recfacialch.myddns.me/api/fichar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos), // Enviar el ID del empleado
        });
        //Esperamos respuesta
        const result = await response.json();
        //console.log(typeof result); // Mostrar el mensaje del servidor
        estado.innerHTML = result; // Mostrar el mensaje del servidor
        if (result==2) {estado.innerHTML = `<span style="color: red; font-weight: bold;">SALIDA: ¡Que tengas un buen día!</span>`;}
        else if (result==1) {estado.innerHTML = `<span style="color: green; font-weight: bold;">ENTRADA: ¡Bienvenid@!</span>`;}
    } catch (error) {
        //Si falla mostramos error
        console.error('Error al fichar:', error);
    }
    //Mostramos mensaje de buen día
    

}

//Actualiza el reloj cada segundo
setInterval(updateClock, 1000);
updateClock();
