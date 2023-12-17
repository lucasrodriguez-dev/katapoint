import { validarSesion, validar, mostrarMensaje, obtenerDeUrl, obtenerTorneo, gohome, mostrarPantallaCarga, ocultarPantallaCarga, obtenerKata } from './TheController.js';

const urlAPI = "../controladores/php/PuntuaAPI.php";
const id_torneo = obtenerDeUrl("id_torneo");
const numero_juez = obtenerDeUrl("numero_juez");
const formulario = $("#formulario");
let torneo = null;
let intervalo = null;
let id_enfrentamiento = null;
let nombre_equipo = null;

$(document).ready(async function () {
    ocultarFormulario();
    mostrarMensajeEspera();
    mostrarPantallaCarga();
    $(".btn_cerrarSesion").on("click", function(){
        document.cookie = "sesion_juez=; max-age=0; path=/";
        gohome();
    });
    try {
        validarSesion('juez');
        torneo = await obtenerTorneo(id_torneo);
        ocultarPantallaCarga();

        //cada cuanto consulta pendientes de puntuar
        const tiempoIntervalo = 3000;

        //generamos una consulta automatizada que se repite en el tiempo la cantidad de ms indicada
        intervalo = setInterval(consultarPendientes, tiempoIntervalo);
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
    $("#btn_ingresar").on("click", async function () {
        try {
            let puntaje = $("#puntaje").val();
            validar("puntaje", puntaje);

            let objeto = {
                id_torneo: id_torneo,
                numero_juez: numero_juez,
                id_enfrentamiento: id_enfrentamiento,
                nombre_equipo: nombre_equipo,
                puntaje: puntaje
            }
            await guardar(objeto);
            setTimeout(function () {
                location.reload();
            }, 2000);
        } catch (e) {
            mostrarMensaje(e, "amarillo");
        }
    });

});

async function consultarPendientes() {
    let url = `${urlAPI}?id_torneo=${id_torneo}&numero_juez=${numero_juez}`;
    await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error("Error: " + respuesta.status);
            }
            return respuesta.json();
        })
        .then(function (datos) {
            console.log(datos);
            if (datos.error) {
                let error = datos.error;
                mostrarMensaje(error, 'rojo');
            } else {
                let pendientes = datos.retorno;
                //si tiene algun equipo pendiente de puntuar
                if (pendientes.length > 0) {
                    //detenemos la consulta automatizada
                    clearInterval(intervalo);
                    ocultarMensajeEspera();

                    let equipo = pendientes[0];
                    id_enfrentamiento = equipo.id_enfrentamiento;
                    nombre_equipo = equipo.nombre_equipo;
                    mostrarFormulario(equipo);
                }
            }
        })
        .catch(error => console.error(error));
}

function ocultarFormulario() {
    formulario.hide();
}

async function mostrarFormulario(equipo) {
    await mostrarInformacion(equipo);
    formulario.show();
}

function mostrarMensajeEspera() {
    mostrarMensaje("Esperando a que algún equipo realice un kata...", "amarillo");
}

function ocultarMensajeEspera() {
    mostrarMensaje("");
}

async function mostrarInformacion(datos) {
    let categoria = "";
    if (torneo.rangoEdad_torneo != null) {
        categoria = torneo.rangoEdad_torneo;
    }
    if (torneo.sexo_torneo == "masculino") {
        categoria += "M";
    } else {
        categoria += "F";
    }
    let kata = await obtenerKata(datos.id_kata);
    $("#torneo").html(`Categoría: ${categoria}`);
    $("#kata").html(`Kata: ${kata.nombre_kata}`);
    $("#equipo").html(`${datos.nombre_equipo}`);
}

async function guardar(objeto) {
    fetch(urlAPI, {
        method: 'POST',
        body: JSON.stringify(objeto),
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error("Error: " + respuesta.status);
            }
            return respuesta.json();
        })
        .then(function (datos) {
            console.log(datos);
            if (datos.error) {
                mostrarMensaje(datos.error, "rojo");
            } else {
                ocultarFormulario();
                mostrarMensaje(datos.retorno, "verde");
            }
        })
        .catch(function (error) {
            console.log(error);
        });
}