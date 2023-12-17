<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Juez.php');

    $respuesta = array();

    try {
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        $metodoSolicitud = $_SERVER["REQUEST_METHOD"];
        $retorno = null;
        $error = "";
        switch(strtoupper($metodoSolicitud)) {
            case 'GET':
                $error = "No se pudo iniciar sesión";
                if(isset($_GET['id_torneo']) && isset($_GET['numero_juez']) && isset($_GET['clave_juez'])){
                    $id_torneo = $_GET['id_torneo'];
                    $numero_juez = $_GET['numero_juez'];
                    $clave_juez = $_GET['clave_juez'];
                    $juez = new Juez($id_torneo, $numero_juez, $clave_juez);
                    if(!$juez->claveCorrecta()){
                        throw new Exception("Número de juez o clave incorrectos");
                    }
                    $url = "ingresarPuntaje.html";
                    $respuesta['redirect'] = $url;
                    $respuesta['sesion'] = $juez->getIdTorneo() . ',' . $juez->getNumero();
                    $retorno = "Se inició sesión";
                }
                break;
            case 'POST':
                $error = "Sesión no válida";
                validarTecnicoSoftware();
                $error = "No se pudo registrar al juez";
                validarPOST($datos);
                $juez = new Juez($datos->id_torneo, $datos->numero_juez, $datos->clave_juez, $datos->nombreCompleto_juez);
                $juez->guardar();
                $retorno = "Juez registrado";
                $respuesta['id_torneo'] = $juez->getIdTorneo();
                break;
            default:
                throw new Exception("Método no permitido");  
        }
        $respuesta['retorno'] = $retorno;
    } catch (Exception $e) {
        $respuesta['error'] = $error . ": " . $e->getMessage();
    } finally {
        echo json_encode($respuesta);
    }

    function validarPOST($datos){
        $parametros = [
            ["nombre" => "id_torneo", "obligatorio" => true],
            ["nombre" => "numero_juez", "obligatorio" => true],
            ["nombre" => "clave_juez", "obligatorio" => true],
            ["nombre" => "nombreCompleto_juez", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>