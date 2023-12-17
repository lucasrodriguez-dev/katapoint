import { validarSesion, validar, mostrarMensaje, obtenerTorneo, obtenerDeUrl, gohome, limpiarCampos } from './TheController.js';

const urlAPI = "../controladores/php/EquipoAPI.php";

$(document).ready(async function () {
    try {
        validarSesion('tecnicoSoftware');
        const torneo = await obtenerTorneo(obtenerDeUrl('id_torneo'));
        if (torneo.modalidad_torneo == "individual") {
            window.history.back();
        }
        $("#btn_ingresar").on("click", async function () {
            try {
                let nombre = $("#nombre").val();
                validar('nombre', nombre);
                const torneo = await obtenerTorneo(obtenerDeUrl('id_torneo'));
                let sexo = torneo.sexo_torneo;
                let cantidadCompetidores = 3;
                let cinturon = "aka";
                let id_torneo = torneo.id_torneo;
                let id_grupo = 1;
                let equipo = {
                    nombre_equipo: nombre,
                    sexo_equipo: sexo,
                    cantidadCompetidores_equipo: cantidadCompetidores,
                    cinturon_equipo: cinturon,
                    id_torneo: id_torneo,
                    id_grupo: id_grupo
                };
                await guardar(urlAPI, equipo);
            } catch (e2) {
                mostrarMensaje(e2, 'amarillo');
            }
        });
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
});

async function guardar(url, objeto) {
    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(objeto)
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error("Error: " + respuesta.status);
            }
            return respuesta.json();
        })
        .then(function (datos) {
            console.table(datos);
            if (datos.error) {
                mostrarMensaje(datos.error, 'rojo');
            }else{
                mostrarMensaje(datos.retorno, 'verde');
            }
        })
        .catch(error => console.error(error));
}