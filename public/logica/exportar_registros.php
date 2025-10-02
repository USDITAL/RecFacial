<?php
// filepath: c:\xampp\htdocs\Proyecto-DAW\public\exportar_registros.php
require($_SERVER['DOCUMENT_ROOT'] . '/Proyecto-DAW/vendor/autoload.php');
require($_SERVER['DOCUMENT_ROOT'] . '/Proyecto-DAW/public/logica/empleado_datos.php');

use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del cuerpo de la solicitud POST
    $datos = isset($_POST['datos']) ? $_POST['datos'] : null;
    
    if ($datos) {
        // Decodificar si los datos vienen como JSON
        if ($_POST['tipo'] == 'csv') {
            $registros = is_string($datos) ? json_decode($datos, true) : $datos;
            // Configurar cabeceras para descarga CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=exportacion_' . date('Y-m-d') . '.csv');
            // Crear el archivo de salida
            $output = fopen('php://output', 'w');
            // Escribir encabezados (asumiendo que todos los registros tienen la misma estructura)
            if (!empty($registros)) {
                fputcsv($output, array_keys($registros[0]));
                foreach ($registros as $registro) {
                    fputcsv($output, $registro);
                }
            }
            fclose($output);
            exit;
        } elseif ($_POST['tipo'] == 'xls') {
            $registros = is_string($datos) ? json_decode($datos, true) : $datos;
            $cabeceras = array_keys($registros[0]);
        
            // Exportar a Excel utilizando HTML
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=registros.xls');
        
            echo "<table border='1'>";
            
            echo '<tr>';
            foreach ($cabeceras as $cabecera) {
                // Opcional: transformar nombres de campos a más legibles
                $nombreLegible = str_replace('_', ' ', ucfirst($cabecera));
                echo '<th>' . htmlspecialchars($nombreLegible) . '</th>';
            }
            echo '</tr>';
            foreach ($registros as $registro) {
                echo '<tr>';
                foreach ($cabeceras as $campo) {
                    echo '<td>' . htmlspecialchars($registro[$campo] ?? '') . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
            exit;
            } elseif ($_POST['tipo'] == 'pdf') {
                $html = "<!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Exportación de Registros</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid black; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    img { max-width: 100px; height: auto; }
                        .cabecera_trans, .registro_header {
                            display: table;
                            width: 100%;
                            border-collapse: collapse;
                            background-color: #333;
                            color: white;
                        }
                            .linea_trans {
                            display: table;
                            width: 100%;
                            border-collapse: collapse;
                            background-color: #fff;
                            color: black;
                        }
                        .linea_trans span, .cabecera_trans span {
                            display: table-cell;
                            padding: 8px;
                        }
                </style>
            </head>
            <body>
                <h1>Exportación de Registros</h1>
                $datos
            </body>
            </html>";
            
                $options = new Options();
                $options->set('isHtml5ParserEnabled', true); 
                $options->set('isRemoteEnabled', true); // Permitir imágenes base64
                //file_put_contents('debug_html.html', $html);

                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $dompdf->stream("document.pdf");
                exit;
            }else {
                echo "Tipo de exportación no válido.";
                exit;
            }
   
        

    } else {
        http_response_code(400);
        echo "Error: No se recibieron datos para exportar";
    }
} else {
    http_response_code(405);
    echo "Error: Método no permitido. Se requiere POST";
}

















