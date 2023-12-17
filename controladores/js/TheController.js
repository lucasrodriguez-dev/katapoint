export function validar(nombreAtributo, valor) {
    if (!valor) {
        throw `Ingrese ${nombreAtributo.toLowerCase()}`;
    }
}

export function validarSesion(tipoUsuario) {
    let sesion = "";
    switch (tipoUsuario) {
        case 'tecnicoSoftware':
            sesion = getCookie("sesion_tecnicoSoftware");
            break;
        case 'juez':
            sesion = getCookie("sesion_juez");
            break;
        default:
            sesion = null;
            break;
    }
    if (sesion === null) {
        throw "No se inició sesión";
    }
}

export function mostrarMensaje(unMensaje, unColor) {
    let color = "";
    switch (unColor) {
        case "verde":
            color = "green";
            break;
        case "amarillo":
            color = "yellow";
            break;
        case "rojo":
            color = "red";
            break;
        default:
            color = "black";
            break;
    }
    $("#mensaje").html(unMensaje);
    $("#mensaje").css("color", color);
}


export async function obtenerTorneo(unaId) {
    try {
        let urlAPI = `../controladores/php/TorneoAPI.php?id_torneo=${unaId}`;
        const respuesta = await fetch(urlAPI, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        if (!respuesta.ok) {
            throw new Error('Error al obtener el Torneo');
        }
        const datosRespuesta = await respuesta.json();
        const torneo = datosRespuesta.retorno;
        console.log(torneo);
        return torneo;
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

export async function obtenerKata(unaId) {
    try {
        let urlAPI = `../controladores/php/KataAPI.php?id_kata=${unaId}`;
        const respuesta = await fetch(urlAPI, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        if (!respuesta.ok) {
            throw new Error('Error al obtener el Kata');
        }
        const datosRespuesta = await respuesta.json();
        const kata = datosRespuesta.retorno;
        console.log(kata);
        return kata;
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

export async function obtenerCompetidores(unEquipo){
    try {
        let urlAPI = `../controladores/php/CompetidorAPI.php?nombre_equipo=${unEquipo}`;
        const respuesta = await fetch(urlAPI, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        if (!respuesta.ok) {
            throw new Error('Error al obtener el Competidor');
        }
        const datosRespuesta = await respuesta.json();
        const competidores = datosRespuesta.retorno;
        console.log(competidores);
        return competidores;
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

export function obtenerDeUrl(parametro) {
    let url = new URL(window.location.href);
    if (parametro) {
        //obtengo el valor del parametro de la url
        let retorno = url.searchParams.get(parametro);
        return retorno;
    }
    return url;
}

export function gohome(delay) {
    if (!delay) {
        delay = 0;
    }
    const inicio = "../index.html";
    redirect(inicio, delay);
}

export function redirect(url, delay) {
    if (url) {
        if (!delay) {
            delay = 0;
        }
        setTimeout(function () {
            window.location.href = url;
        }, delay);
    }
}

//ARREGLAR
export function limpiarCampos() {
    //por cada input
    $('input').each(function () {
        //$(this) = 'input' actual
        $(this).val("");
    });
}

export function print(unElemento, unValor, unColor) {
    if (!unElemento) {
        return null;
    }
    if ($(unElemento).length === 0) {
        console.log("Elemento no existente en el DOM");
        return null;
    }
    let color = "";
    switch (unColor) {
        case "verde":
            color = "green";
            break;
        case "amarillo":
            color = "yellow";
            break;
        case "rojo":
            color = "red";
            break;
        default:
            color = $("body").css("color");
            break;
    }
    $(unElemento).html(unValor);
    $(unElemento).css("color", color);
}

export function getCookie(nombre) {
    const cookie = document.cookie
        .split('; ')
        .find(cookie => cookie.startsWith(nombre + '='));

    if (cookie) {
        return cookie.split('=')[1];
    }
    return null;
}

export function mostrarPantallaCarga() {
    //le aplicamos un filtro de desenfoque al body
    $("body").css("filter", "blur(5px)");
}

export function ocultarPantallaCarga() {
    //quitamos filtro de desenfoque al body 
    $("body").css("filter", "blur(0)");
}

export function estiloTabla(){
    return `style="
    width:70vw;
    margin-left:13vw;
    font-weight: bold;
    "`;
}
