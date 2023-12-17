import { validarSesion, validar, mostrarMensaje, estiloTabla, redirect, obtenerTorneo, obtenerDeUrl, gohome, mostrarPantallaCarga, ocultarPantallaCarga } from "./TheController.js";

const urlAPI = "../controladores/php/CompetidorAPI.php";
const id_torneo = obtenerDeUrl('id_torneo');
let torneo = null;
let retorno = [];

$(document).ready(async function () {
    mostrarPantallaCarga();
    try {
        validarSesion('tecnicoSoftware');
        torneo = await obtenerTorneo(id_torneo);
        $("#btn_ingresar").on("click", function () {
            redirect(`registrarCompetidor.html?id_torneo=${id_torneo}`);
        });
        try {
            await construirTabla();
            ocultarPantallaCarga();
            console.table(retorno);
        } catch (e2) {
            $("#mensaje").html(e2);
            $("#mensaje").css("color", "verde");
        }

        let filaModificada = null;
        let id = null;
        let id_competidor = null;
        $(".btn_modificar").on("click", async function () {

            id = $(this).attr("id");
            id_competidor = id.split("-")[1];

            //obtenemos la fila
            filaModificada = $(this).closest("tr");

            //habilitamos la edicion del contenido de sus celdas
            filaModificada.find("td").prop("contenteditable", true);

            //mostramos boton guardar
            $("#container_guardar").show();
        });

        $(".btn_guardar").on("click", async function () {
            //deshabilitamos la edicion del contenido de las celdas que se intento modificar anteriormente
            filaModificada.find("td").prop("contenteditable", false);

            //obtenemos los valores de las celdas modificadas
            let ci_competidor = $(`#ci_competidor-${id_competidor}`).text();
            let nombreCompleto_competidor = $(`#nombreCompleto_competidor-${id_competidor}`).text();
            let fechaNacimiento_competidor = $(`#fechaNacimiento_competidor-${id_competidor}`).text();
            let escuela_competidor = $(`#escuela_competidor-${id_competidor}`).text();
            let nombre_equipo = $(`#nombre_equipo-${id_competidor}`).text();

            let sexo_competidor = retorno[id_competidor].sexo_competidor;

            let competidor = {
                id_torneo: id_torneo,
                id_competidor: id_competidor,
                ci_competidor: ci_competidor,
                sexo_competidor: sexo_competidor,
                fechaNacimiento_competidor: fechaNacimiento_competidor,
                escuela_competidor: escuela_competidor,
                nombreCompleto_competidor: nombreCompleto_competidor,
                nombre_equipo: nombre_equipo
            }
            console.table(competidor);
            //lo actualizamos
            await actualizar(competidor);
            //ocultamos boton guardar
            $("#container_guardar").hide();
        });

        $(".btn_eliminar").on("click", async function () {
            let opcion = confirm("¿Seguro que quieres eliminarlo?")
            if (opcion) {
                let id = $(this).attr("id");
                let id_competidor = id.split('-')[1];
                let competidor = {
                    id_competidor: id_competidor
                }
                //lo eliminamos
                await eliminar(competidor);
            }
        });
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
});

async function construirTabla() {
    let url = `${urlAPI}?id_torneo=${id_torneo}`;
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
            }
            //declaramos los estilos para la tabla
            let style = estiloTabla();

            let containerTabla = $("#container_tabla");
            let tabla = $(`<table border='1' class='tabla' ${style}>`);

            let encabezadoEquipo = ``;
            let columnaEquipo = ``;

            if (torneo.modalidad_torneo == "equipo") {
                encabezadoEquipo = `<th>EQUIPO</th>`;
            }

            let encabezado = $(`<thead><th>CI</th><th>NOMBRE</th><th>FECHA DE NACIMIENTO</th><th>ESCUELA</th>${encabezadoEquipo}<th colspan='2'>ACCIÓN</th></thead>`);
            tabla.html(encabezado);


            let cantidadCompetidores = datos.retorno.length;
            for (let i = 0; i < cantidadCompetidores; i++) {
                let fila = $("<tr>");
                let competidor = datos.retorno[i];
                retorno[competidor.id_competidor] = competidor;

                if (torneo.modalidad_torneo == "equipo") {
                    columnaEquipo = `<td id='nombre_equipo-${competidor.id_competidor}'>${competidor.nombre_equipo}</td>`;
                }

                let columnaAccion = `<td><input type='button' class='btn btn_modificar' id='btn_modificar-${competidor.id_competidor}' value='Modificar'</td><td><input type='button' class='btn btn_eliminar' id='btn_eliminar-${competidor.id_competidor}' value='Eliminar'</td>`;

                let columnas = $(`<td id='ci_competidor-${competidor.id_competidor}'>${competidor.ci_competidor}</td><td id='nombreCompleto_competidor-${competidor.id_competidor}'>${competidor.nombreCompleto_competidor}</td><td id='fechaNacimiento_competidor-${competidor.id_competidor}'>${competidor.fechaNacimiento_competidor}</td><td id='escuela_competidor-${competidor.id_competidor}'>${competidor.escuela_competidor}</td>${columnaEquipo}${columnaAccion}`);
                fila.html(columnas);
                tabla.append(fila);
            }
            containerTabla.html(tabla);
            let containerGuardar = $("<section id='container_guardar' style='display:none;'>");
            let botonGuardar = "<button class='btn btn_guardar'>Guardar</button>";
            containerGuardar.html(botonGuardar);
            containerTabla.append(containerGuardar);
        })
        .catch(error => console.error(error));
}

async function actualizar(objeto) {
    await fetch(urlAPI, {
        method: 'PUT',
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
            console.log(datos);
            if (datos.error) {
                let error = datos.error;
                alert(error);
            } else {
                alert(datos.retorno);
            }
        })
        .catch(error => console.error(error));
}

async function eliminar(objeto) {
    await fetch(urlAPI, {
        method: 'DELETE',
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
            console.log(datos);
            if (datos.error) {
                let error = datos.error;
                alert(error);
            } else {
                location.reload();
            }
        })
        .catch(error => console.error(error));
}