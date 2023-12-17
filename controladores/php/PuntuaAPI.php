<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');
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
                $error = "No se pudo obtener las puntuaciones";
                if(isset($_GET['id_torneo'])){
                    $id_torneo = $_GET['id_torneo'];
                    if(isset($_GET['numero_juez'])){
                        $numero_juez = $_GET['numero_juez'];
                        validarJuez();
                        $retorno = Juez::listarPendientesDePuntuar($id_torneo, $numero_juez);
                    }else{
                        validarTecnicoSoftware();
                        $retorno = Torneo::listarPendientesDePuntuar($id_torneo);
                    }
                }else{
                    throw new Exception("Parámetros obligatorios: 'id_torneo'. Parámetros opcionales: 'numero_juez'");
                }
                break;
            case 'POST':
                $error = "Sesión no válida";
                validarJuez();
                $error = "No se pudo registrar el puntaje";
                validarPOST($datos);
                $juez = new Juez($datos->id_torneo, $datos->numero_juez);
                if($juez === null){
                    throw new Exception("Juez no existente");
                }
                $juez->puntuar($datos->id_enfrentamiento, $datos->nombre_equipo, $datos->puntaje);
                $retorno = "Puntaje registrado";
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
            ["nombre" => "id_enfrentamiento", "obligatorio" => true],
            ["nombre" => "nombre_equipo", "obligatorio" => true],
            ["nombre" => "puntaje", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>