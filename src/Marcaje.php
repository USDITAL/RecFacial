<?php

namespace Clases;
//Clases a usar
use DateTime;
use DateTimeZone;
use PDO;
use PDOException;

class Marcaje{
    //Atributos
    private int $cod_Marcaje;
    private int $cod_Tipo_Marcaje;
    private int $cod_Empleado;
    private int $cod_bio;
    private DateTime $fec_Marcaje;
    private DateTime $fec_Grabacion;
    private bool $incidencia;
    private bool $pendiente;
    private string $foto;
    private string $tipoAcceso;
    private string $obs;

    //Método constructor
    public function __construct(){
        $this->cod_Marcaje = 0;
        $this->cod_bio = 0; 
        $this->cod_Tipo_Marcaje = 0;
        $this->cod_Empleado = 0;
        $this->fec_Marcaje = new DateTime('now', new DateTimeZone('Europe/Madrid'));
        $this->fec_Grabacion = new DateTime('now', new DateTimeZone('Europe/Madrid'));
        $this->incidencia = false;
        $this->pendiente = false;
        $this->foto = '';
        $this->tipoAcceso = '0';
        $this->obs = '';

    }



    //Destructor
    public function __destruct() {
        unset($this->cod_Marcaje);
        unset($this->cod_Tipo_Marcaje);
        unset($this->cod_Empleado);
        unset($this->cod_bio);
        unset($this->fec_Marcaje);
        unset($this->fec_Grabacion);
        unset($this->incidencia);
        unset($this->pendiente);
        unset($this->foto);
        unset($this->tipoAcceso);
        unset($this->obs);
    }

    //Calcular las fechas de la semana (De lunes a domingo)
    public function calcularFechasSemana(DateTime $fecha): array {
        // Clona la fecha para no modificar el objeto original
        $fechaInicio = clone $fecha;
    
        // Ajusta al lunes de la semana actual
        $diaSemana = (int) $fechaInicio->format('N'); // 1 (lunes) a 7 (domingo)
        $fechaInicio->modify('-' . ($diaSemana - 1) . ' days');
    
        // Genera las fechas de lunes a domingo
        $fechasSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $fechasSemana[] = (clone $fechaInicio)->modify("+{$i} days");
        }
    
        return $fechasSemana;
    }

    //Método para sumar las horas trabajadas en la semana a partir de una fecha.
    public function calcularHorasSemana(int $codEmpleado, DateTime $fecha,int $cmin, int $cmax): float {
        // Obtiene las fechas de la semana
        $fechasSemana = $this->calcularFechasSemana($fecha);
    
        // Suma las horas trabajadas de cada día
        $totalHoras = 0.0;
        foreach ($fechasSemana as $dia) {
            $totalHoras += $this->calcularHorasTrabajadas($codEmpleado, $dia, $cmin, $cmax);
        }
    
        return $totalHoras;
    }

    //Método para obtener el TIPO del último marcaje 
    public function ultimoMarcaje($empleado){
        try{
            //Crea una conexión y una consulta SELECT
            $conexion = new Conexion();
            $consulta = $conexion->conexion->prepare("SELECT COD_TIPO_MARCAJE FROM tmarcaje WHERE COD_EMPLEADO = :cod AND COD_TIPO_ACCESO<900 ORDER BY FEC_MARCAJE DESC LIMIT 1");
            //Parametriza y ejecuta
            $consulta->bindValue(':cod', $empleado, PDO::PARAM_INT);
            $consulta->execute();
            //Vuelca el resultado
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            //Devuelve el tipo de marcaje para saber si entra (1) o sale (2)
            return $resultado['COD_TIPO_MARCAJE'];
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al obtener marcaje: " . $e->getMessage());
            return false;
        }
    }

    //Método para calcular las horas trabajadas de más
    public function calcularBolsaMensual(int $codEmpleado, DateTime $fecha): void {
        try {
            // Obtiene el empleado y su configuración
            $empleado = new Empleado();
            $empleado->cargarDatosEmpleado($codEmpleado);
            $maxHorasDia = $empleado->getMaxHorasDia();
            $bolsaActual = $empleado->getBolsa();
    
            // Obtiene el primer y último día del mes de la fecha consultada
            $fechaInicio = (clone $fecha)->modify('first day of this month');
            $fechaFin = (clone $fecha)->modify('last day of this month');
    
            // Obtiene los días del mes con registros
            $conexion = new Conexion();
            $consulta = $conexion->conexion->prepare("
                SELECT DISTINCT DATE(FEC_MARCAJE) AS dia
                FROM tmarcaje
                WHERE COD_EMPLEADO = :codEmpleado AND COD_TIPO_ACCESO < 90
                AND FEC_MARCAJE BETWEEN :fechaInicio AND :fechaFin
            ");
            $consulta->bindValue(':codEmpleado', $codEmpleado, PDO::PARAM_INT);
            $consulta->bindValue(':fechaInicio', $fechaInicio->format('Y-m-d 00:00:00'));
            $consulta->bindValue(':fechaFin', $fechaFin->format('Y-m-d 23:59:59'));
            $consulta->execute();
            $diasConRegistros = $consulta->fetchAll(PDO::FETCH_COLUMN);
    
            // Inicializa el acumulador de horas extras
            $totalHorasExtras = 0.0;
    
            // Calcula las horas trabajadas y las horas extras para cada día con registros
            foreach ($diasConRegistros as $dia) {
                $fechaDia = new DateTime($dia, new DateTimeZone('Europe/Madrid'));
                $horasTrabajadas = $this->calcularHorasTrabajadas($codEmpleado, $fechaDia,0,89);
    
                // Calcula las horas extras del día
                $horasExtras = $horasTrabajadas - $maxHorasDia;
                $totalHorasExtras += $horasExtras;
            }
    
            // Actualiza la bolsa del empleado
            $nuevaBolsa = $totalHorasExtras;
            $empleado->setBolsa($nuevaBolsa);
            $empleado->grabar();
    
            // Log de éxito
            error_log("Bolsa mensual calculada y actualizada para el empleado $codEmpleado. Nueva bolsa: $nuevaBolsa");
        } catch (Exception $e) {
            // Manejo de errores
            error_log("Error al calcular la bolsa mensual: " . $e->getMessage());
        }
    }

    public function calcularHorasMensual(int $codEmpleado, DateTime $fecha): array {
        try {
            // Obtiene el empleado y su configuración
            $empleado = new Empleado();
            $empleado->cargarDatosEmpleado($codEmpleado);
            $maxHorasDia = $empleado->getMaxHorasDia();
            $bolsaActual = $empleado->getBolsa();
    
            // Obtiene el primer y último día del mes de la fecha consultada
            $fechaInicio = (clone $fecha)->modify('first day of this month');
            $fechaFin = (clone $fecha)->modify('last day of this month');
    
            // Obtiene los días del mes con registros
            $conexion = new Conexion();
            $consulta = $conexion->conexion->prepare("
                SELECT DISTINCT DATE(FEC_MARCAJE) AS dia
                FROM tmarcaje
                WHERE COD_EMPLEADO = :codEmpleado AND COD_TIPO_ACCESO < 90
                AND FEC_MARCAJE BETWEEN :fechaInicio AND :fechaFin
            ");
            $consulta->bindValue(':codEmpleado', $codEmpleado, PDO::PARAM_INT);
            $consulta->bindValue(':fechaInicio', $fechaInicio->format('Y-m-d 00:00:00'));
            $consulta->bindValue(':fechaFin', $fechaFin->format('Y-m-d 23:59:59'));
            $consulta->execute();
            $diasConRegistros = $consulta->fetchAll(PDO::FETCH_COLUMN);
    
            // Inicializa el acumulador de horas extras
            $totalHorasExtras = 0.0;
            $totalHorasNormales =0.0;
    
            // Calcula las horas trabajadas y las horas extras para cada día con registros
            foreach ($diasConRegistros as $dia) {
                $fechaDia = new DateTime($dia, new DateTimeZone('Europe/Madrid'));
                $horasTrabajadas = $this->calcularHorasTrabajadas($codEmpleado, $fechaDia,0,89);
    
                // Calcula las horas extras del día
                $horasExtras = $horasTrabajadas - $maxHorasDia;
                if($horasExtras>0){$totalHorasNormales += $maxHorasDia;}else{$totalHorasNormales += $horasTrabajadas;}
                $totalHorasExtras += $horasExtras;
            }
            $horasfinales=[
                'Normales' => $totalHorasNormales,
                'Extras' => $totalHorasExtras
            ];
            return $horasfinales;
        } catch (Exception $e) {
            // Manejo de errores
            error_log("Error al calcular la bolsa mensual: " . $e->getMessage());
            return [];
        }
    }

    //Método que devuelve las horas trabajadas en la fecha indicada y entre los tipos de acceso especificados
    public function calcularHorasTrabajadas(int $codEmpleado, DateTime $fecha, int $cMin, int $cMax): float {
    try {
        // Obtiene los marcajes del día
        $marcajesDelDia = array_filter(
            $this->marcajesHoy($codEmpleado, $fecha),
            function ($registro) use ($cMin, $cMax){
                return $registro['COD_TIPO_ACCESO'] >= $cMin && $registro['COD_TIPO_ACCESO'] <= $cMax;
            }
        );

        // Inicializa las variables para el cálculo
        $horasTrabajadas = 0.0;
        $ultimoMarcaje = null;

        foreach ($marcajesDelDia as $marcaje) {
            // Obtiene el tipo y fecha del marcaje
            $tipoMarcaje = $marcaje['COD_TIPO_MARCAJE'];
            $fechaMarcaje = new DateTime($marcaje['FEC_MARCAJE']);

            if ($tipoMarcaje == 1) {
                // Si es un marcaje de entrada, guarda el marcaje para restarlo luego
                $ultimoMarcaje = $fechaMarcaje;
            } elseif ($tipoMarcaje == 2 && $ultimoMarcaje !== null) {
                // Si es un marcaje de salida, calcula la diferencia con el último marcaje de entrada
                $timestampInicio = $ultimoMarcaje->getTimestamp();
                $timestampFin = $fechaMarcaje->getTimestamp();

                // Calcula la diferencia en segundos
                $diferenciaSegundos = $timestampFin - $timestampInicio;

                // Convierte la diferencia a horas y minutos
                $horas = floor($diferenciaSegundos / 3600); // 1 hora = 3600 segundos
                $minutos = floor(($diferenciaSegundos % 3600) / 60); // Resto en minutos

                // Suma las horas y los minutos como fracción de hora
                $horasTrabajadas += $horas + ($minutos / 60);

                // Resetea el último marcaje
                $ultimoMarcaje = null;
            }
        }

        // Si el último marcaje es de tipo entrada, calcula el tiempo hasta ahora
        if ($ultimoMarcaje !== null) {
            $timestampInicio = $ultimoMarcaje->getTimestamp();
            $timestampFin = (new DateTime())->getTimestamp();

            // Calcula la diferencia en segundos
            $diferenciaSegundos = $timestampFin - $timestampInicio;

            // Convierte la diferencia a horas y minutos
            $horas = floor($diferenciaSegundos / 3600);
            $minutos = floor(($diferenciaSegundos % 3600) / 60);

            // Suma las horas y los minutos como fracción de hora
            $horasTrabajadas += $horas + ($minutos / 60);
        }

        // Devuelve las horas trabajadas con precisión de dos decimales
        return round($horasTrabajadas, 2);
    } catch (Exception $e) {
        // Manejo de errores
        error_log("Error al calcular las horas trabajadas: " . $e->getMessage());
        return 0.0;
    }
}
    //Método para comprobar asistencia del día
    public function asistencia($empleado, DateTime $fecha){
        // 0 = Sin registros, 1 = Entrada registrada, 2 = Salida registrada
        // Primero verificamos si hay algún marcaje del empleado en la fecha
        $marcajesHoy = $this->marcajesHoy($empleado, $fecha);
        if (empty($marcajesHoy)) {
            return 0; // No hay registros para esta fecha
        }
        $ultimo = $this->ultimoMarcaje($empleado);
        return $ultimo; // Devuelve 1 o 2 según el último registro
    }

    //Método para obtener marcajes del día
    public function marcajesHoy($empleado, DateTime $fecha):array{
        try{
            //Crea una conexión y una consulta SELECT
            $conexion = new Conexion();
            //consulta ascendente de los marcaje de fecha(No tiene en cuenta hora)
            $consulta = $conexion->conexion->prepare("SELECT * FROM tmarcaje WHERE COD_EMPLEADO = :cod AND DATE(FEC_MARCAJE) = :fec ORDER BY FEC_MARCAJE ASC");
            //Parametriza y ejecuta
            $consulta->bindValue(':cod', $empleado, PDO::PARAM_INT);
            $consulta->bindValue(':fec', $fecha->format('Y-m-d'));
            $consulta->execute();
            //Vuelca el resultado
            $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            //Devuelve array con el tipo y la fecha de cada marcaje
            return $resultado;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al obtener marcaje: " . $e->getMessage());
            return [];
        }
    }

    //Método para marcar de una sola vez, rellena todos los parámetros
    public function marcar($tipo,$empleado,$cod_bio,$fec_Mar,$fec_Grab,$incidencia,$pendiente,$foto,$tipo_acceso,$obs){
        try{$this->setCodTipoMarcaje($tipo);
        $this->setCodEmpleado($empleado);
        $this->setCodBio($cod_bio);
        $this->setFecMarcaje(new DateTime($fec_Mar));
        $this->setFecGrabacion(new DateTime($fec_Grab));
        $this->setIncidencia($incidencia);
        $this->setPendiente($pendiente);
        $this->setFoto($foto);
        $this->setTipoAcceso($tipo_acceso);
        $this->setObs($obs);
        $this->grabar();
        return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al marcar: " . $e->getMessage());
            return false;
        }
    }

    //Método para obtener Lista de tipos de marcaje
    public function listaTiposAcceso(): array{
        try{
            $conexion = new Conexion();
            //Consulta de la tabla ttipoacceso
            $sql = "SELECT * FROM ttipoacceso";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resultadoAsoc = array_column($resultado, 'DES_TIPO_ACCESO', 'COD_TIPO_ACCESO');
            //Devuelve resutlados
            return $resultadoAsoc;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar tipos de acceso: " . $e->getMessage());
            return [];
        }
    }



    //Método para registrar el marcaje en la bbdd
    public function grabar(): bool {
        $inci=0;
        $resinci=0;
        try{
            $conexion = new Conexion();
            //Si no hay cod_Marcaje prepara un INSERT
            if ($this->cod_Marcaje==0 || is_null($this->cod_Marcaje)){
                $sql = "INSERT INTO tmarcaje (COD_TIPO_MARCAJE, COD_EMPLEADO, COD_BIO, DES_FOTO, FEC_MARCAJE, FEC_GRABACION, IND_INCIDENCIA, IND_PENDIENTE, COD_TIPO_ACCESO, DES_OBSERVACIONES) 
                VALUES (:COD_TIPO_MARCAJE, :COD_EMPLEADO, :COD_BIO, :DES_FOTO, :FEC_MARCAJE, :FEC_GRABACION, :IND_INCIDENCIA, :IND_PENDIENTE, :COD_TIPO_ACCESO, :DES_OBSERVACIONES)";
                $stmt = $conexion->conexion->prepare($sql);
            //Si hay cod_Marcaje prepara un UPDATE
            }else{
                $sql ="UPDATE tmarcaje SET COD_TIPO_MARCAJE = :COD_TIPO_MARCAJE, COD_EMPLEADO = :COD_EMPLEADO
                , COD_BIO = :COD_BIO, DES_FOTO=:DES_FOTO, FEC_MARCAJE=:FEC_MARCAJE
                , FEC_GRABACION=:FEC_GRABACION, IND_INCIDENCIA=:IND_INCIDENCIA, IND_PENDIENTE=:IND_PENDIENTE
                , COD_TIPO_ACCESO= :COD_TIPO_ACCESO, DES_OBSERVACIONES =:DES_OBSERVACIONES 
                WHERE COD_MARCAJE=:cod_Marcaje";
                $stmt = $conexion->conexion->prepare($sql);
                $stmt->bindValue(':cod_Marcaje', $this->cod_Marcaje);
            }
            //Parametriza la consulta
            if ($this->incidencia){$inci=1;}else{$inci=0;}
            if ($this->pendiente){$resinci=1;}else{$resinci=0;}
            $stmt->bindValue(':COD_TIPO_MARCAJE', $this->cod_Tipo_Marcaje);
            $stmt->bindValue(':COD_EMPLEADO', $this->cod_Empleado);
            $stmt->bindValue(':COD_BIO', $this->cod_bio);
            $stmt->bindValue(':DES_FOTO', $this->foto);
            $stmt->bindValue(':FEC_MARCAJE', $this->fec_Marcaje->format('Y-m-d H:i:s'));
            $stmt->bindValue(':FEC_GRABACION', $this->fec_Grabacion->format('Y-m-d H:i:s'));
            $stmt->bindValue(':IND_INCIDENCIA',$inci);
            $stmt->bindValue(':IND_PENDIENTE', $resinci);
            $stmt->bindValue(':COD_TIPO_ACCESO', $this->tipoAcceso);
            $stmt->bindValue(':DES_OBSERVACIONES', $this->obs);
            //Ejecuta la consulta
            $stmt->execute();

            // Si es un marcaje de salida, actualiza la bolsa de horas
            if ($this->cod_Tipo_Marcaje == 2) {
                $this->actualizarBolsaHoras();
            }
            $this->setCodMarcaje($this->ultimoMarcaje($this->cod_Empleado));
            //Elimina el objeto conexión
            $conexion = null;
            //Devuelve true
            return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al grabar marcaje: " . $e->getMessage());
            return false;
        }
    }

    //Actualiza la bolsa de horas del empleado
    private function actualizarBolsaHoras(): void {
        try {
            // Obtiene la jornada total del día
            $horasTrabajadasHoy = $this->calcularHorasTrabajadas($this->cod_Empleado, new DateTime($this->fec_Marcaje->format('Y-m-d')),0,90);
    
            // Obtiene el empleado y su bolsa actual
            $empleado = new Empleado();
            $empleado->cargarDatosEmpleado($this->cod_Empleado);
            $bolsaActual = $empleado->getBolsa();
            $maxHorasDia = $empleado->getMaxHorasDia();
    
            // Calcula la nueva bolsa
            $nuevaBolsa = $bolsaActual + ($horasTrabajadasHoy - $maxHorasDia);
            
            //Graba la bolsa de horas actual
            $empleado->setBolsa($nuevaBolsa);
            $empleado->grabar();
    
            // Elimina el objeto conexión
            $conexion = null;
        } catch (Exception $e) {
            error_log("Error al actualizar la bolsa de horas: " . $e->getMessage());
        }
    }
    //Método para cargar los datos de un marcaje, devuelve objeto Marcaje
    public function cargar(int $cod_Marcaje): Marcaje {
        try{
            //Crea la conexión y prepara la consulta SELECT
            $conexion = new Conexion();
            $consulta = $conexion->conexion->prepare("SELECT * FROM tmarcaje WHERE COD_MARCAJE = :cod_Marcaje");
            //Parmetriza y ejecuta
            $consulta->bindParam(':cod_Marcaje', $cod_Marcaje);
            $consulta->execute();
            //Vuelca el resultado
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            if (!$resultado) {
                //si no hay resultado devuelve false
                return $resultado;
            }
            //volcamos el resultado en los parámetros
            $this->cod_Marcaje = $resultado['COD_MARCAJE'];
            $this->cod_Tipo_Marcaje = $resultado['COD_TIPO_MARCAJE'];
            $this->cod_Empleado = $resultado['COD_EMPLEADO'];
            $this->cod_bio = $resultado['COD_BIO'];
            $this->fec_Marcaje = new DateTime($resultado['FEC_MARCAJE']);
            $this->fec_Grabacion = new DateTime($resultado['FEC_GRABACION']);
            $this->incidencia = $resultado['IND_INCIDENCIA'];
            $this->pendiente = $resultado['IND_PENDIENTE'];
            $this->foto = $resultado['DES_FOTO'];
            $this->tipoAcceso = $resultado['COD_TIPO_ACCESO'];
            $this->obs = $resultado['DES_OBSERVACIONES'];
            //devuelve el objeto mismo
            return $this;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar marcaje: " . $e->getMessage());
            return $this;
        }
    }

        //Método para cargar conjunto de marcajes entre fechas, devuelve array de registros
        public function cargarMarcajesEntreFechas(int $empleadoI, int $empleadoF, DateTime $fechaInicio, DateTime $fechaFin): array {
            try{
                //Crea conexión de tipo SELECT
                $conexion = new Conexion();
                $consulta = $conexion->conexion->prepare("
                    SELECT * FROM tmarcaje 
                    WHERE FEC_MARCAJE BETWEEN :fechaInicio AND :fechaFin 
                    AND COD_EMPLEADO BETWEEN :empleadoI AND :empleadoF 
                    ORDER BY FEC_MARCAJE ASC
                ");
                //Parametriza y ejecuta
                $consulta->bindValue(':empleadoI', $empleadoI);
                $consulta->bindValue(':empleadoF', $empleadoF);
                $consulta->bindValue(':fechaInicio', $fechaInicio->format('Y-m-d H:i:s'));
                $consulta->bindValue(':fechaFin', $fechaFin->format('Y-m-d H:i:s'));
                $consulta->execute();
                //Vuelca el resultado
                $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
                
                //Devuelve el resultado
                return $resultado;
            }catch(PDOException $e){
                //Muestra error y devuelve array vacío
                error_log("Error al cargar marcajes: " . $e->getMessage());
                return [];
            }
            
        }

        //Lista de marcajes entre fechas, usuarios y tipo de marcaje
        //Método para cargar conjunto de marcajes entre fechas, devuelve array de registros
        public function cargarMarcajesFiltro(int $empleadoI, int $empleadoF, DateTime $fechaInicio, DateTime $fechaFin,int $tipoInicio, int $tipoFin): array {
            try{
                if ($fechaInicio == $fechaFin){
                    $fechaFin->modify('+1 day');
                }
                //Crea conexión de tipo SELECT
                $conexion = new Conexion();
                $consulta = $conexion->conexion->prepare("
                    SELECT * FROM tmarcaje 
                    WHERE FEC_MARCAJE BETWEEN :fechaInicio AND :fechaFin 
                    AND COD_EMPLEADO BETWEEN :empleadoI AND :empleadoF AND COD_TIPO_MARCAJE BETWEEN :tipoI AND :tipoF 
                    ORDER BY FEC_MARCAJE ASC
                ");
                //Parametriza y ejecuta
                $consulta->bindValue(':empleadoI', $empleadoI);
                $consulta->bindValue(':empleadoF', $empleadoF);
                $consulta->bindValue(':fechaInicio', $fechaInicio->format('Y-m-d H:i:s'));
                $consulta->bindValue(':fechaFin', $fechaFin->format('Y-m-d H:i:s'));
                $consulta->bindValue(':tipoI', $tipoInicio);
                $consulta->bindValue(':tipoF', $tipoFin);
                
                $consulta->execute();
                //Vuelca el resultado
                $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
                
                //Devuelve el resultado
                return $resultado;
            }catch(PDOException $e){
                //Muestra error y devuelve array vacío
                error_log("Error al cargar marcajes: " . $e->getMessage());
                return [];
            }
            
        }
    
        //Obtener los últimos marcajes en un array descendente
        public function obtenerUltimosMarcajes(int $codEmpleado, int $limite = 5): array {
            try {
                // Crea la conexión y prepara la consulta SELECT
                $conexion = new Conexion();
                $consulta = $conexion->conexion->prepare("SELECT * 
                    FROM tmarcaje 
                    WHERE COD_EMPLEADO = :codEmpleado 
                    ORDER BY FEC_MARCAJE DESC 
                    LIMIT :limite
                ");
                // Parametriza y ejecuta
                $consulta->bindValue(':codEmpleado', $codEmpleado, PDO::PARAM_INT);
                $consulta->bindValue(':limite', $limite, PDO::PARAM_INT);
                $consulta->execute();
                // Vuelca el resultado
                $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);

                // Convierte las fechas de UTC a Europe/Madrid
                //foreach ($resultado as &$marcaje) {
                //    $marcaje['FEC_MARCAJE'] = $marcaje['FEC_MARCAJE'];
                //}
                //Devuelve el resultado
                return $resultado;
            } catch (PDOException $e) {
                // Muestra error y devuelve un array vacío
                error_log("Error al obtener los últimos marcajes: " . $e->getMessage());
                return [];
            }
        }
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<   GETTERS Y SETTERS >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    // Getters
    public function getCodMarcaje(): int {
        return $this->cod_Marcaje;
    }

    public function getCodTipoMarcaje(): int {
        return $this->cod_Tipo_Marcaje;
    }

    public function getCodEmpleado(): int {
        return $this->cod_Empleado;
    }

    public function getCodBio(): int {
        return $this->cod_bio;
    }

    public function getFecMarcaje(): DateTime {
        return $this->fec_Marcaje;
    }

    public function getFecGrabacion(): DateTime {
        return $this->fec_Grabacion;
    }

    public function getIncidencia(): bool {
        return $this->incidencia;
    }

    public function getPendiente(): bool {
        return $this->pendiente;
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function getTipoAcceso(): string {
        return $this->tipoAcceso;
    }

    public function getObs(): string {
        return $this->obs;
    }


    // Setters

    public function setCodMarcaje(int $cod_Marcaje): void {
        $this->cod_Marcaje = $cod_Marcaje;
    }

    public function setCodTipoMarcaje(int $cod_Tipo_Marcaje): void {
        $this->cod_Tipo_Marcaje = $cod_Tipo_Marcaje;
    }

    public function setCodEmpleado(int $cod_Empleado): void {
        $this->cod_Empleado = $cod_Empleado;
    }

    public function setCodBio(int $cod_bio): void {
        $this->cod_bio = $cod_bio;
    }

    public function setFecMarcaje(DateTime $fec_Marcaje): void {
        $this->fec_Marcaje = $fec_Marcaje;
    }

    public function setFecGrabacion(DateTime $fec_Grabacion): void {
        $this->fec_Grabacion = $fec_Grabacion;
    }

    public function setIncidencia(bool $incidencia): void {
        $this->incidencia = $incidencia;
    }

    public function setPendiente(bool $pendiente): void {
        $this->pendiente = $pendiente;
    }

    public function setFoto(string $foto): void {
        $this->foto = $foto;
    }

    public function setTipoAcceso(string $tipoAcceso): void {
        $this->tipoAcceso = $tipoAcceso;
    }

    public function setObs(string $obs): void {
        $this->obs = $obs;
    }

}