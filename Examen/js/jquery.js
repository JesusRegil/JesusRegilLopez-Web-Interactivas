// MovieReviews: lógica con jQuery
//Llamada a la API de IMDb a través de RapidAPI
const settings = {
  async: true,
  crossDomain: true,
  url: "https://imdb236.p.rapidapi.com/api/imdb/top250-movies",
  method: "GET",
  headers: {
    "x-rapidapi-key": "857c945e17msh4fb1ce607848a40p1f2e94jsn8540cee8873d",
    "x-rapidapi-host": "imdb236.p.rapidapi.com",
    "Content-Type": "application/json"
  }
};

let peliculas = [];
let peliculaActual = null;
let desde = 0;
let hasta = 9999;
//Solo soporta 9999 peliculas. 

function listaFavoritos() {
  return JSON.parse(localStorage.getItem("favoritos") || "[]");
}

function guardarFavoritos(lista) {
  localStorage.setItem("favoritos", JSON.stringify(lista));
  $("#contadorFavoritos").text(lista.length);
}

function esFavorito(id) {
  return listaFavoritos().some(pelicula => pelicula.id === id);
}
//Esto es para qiue no se rompa la página si no hay poster disponible, se muestra un placeholder
//De todas formas llama a peliculas.json si no hay conexión a la API, pero no tiene imagen (más bien no me cargan). 
function placeholder() {
  return "https://placehold.co/600x900?text=Sin+poster";
}

function texto(valor) {
  if (!valor) return "No disponible";
  if (!Array.isArray(valor)) return valor;
  return valor.map(item => typeof item === "string" ? item : item.name || item.text || "").filter(Boolean).join(", ") || "No disponible";
}

function dinero(valor) {
  if (valor === null || valor === undefined || valor === "") return "No disponible";
  return typeof valor === "number" ? "$" + valor.toLocaleString("es-MX") : valor;
}
//Esta función toma un valor de rating y devuelve el HTML correspondiente con estrellas y el número de rating
//La verdad esto si lo hizo la IA, no yo, pero me pareció muy buena idea y la puse aquí.

function estrellas(valor) {
  const rating = Number(valor) || 0;
  let html = "";
  for (let i = 1; i <= 5; i++) {
    html += `<i class="bi ${i <= Math.round(rating / 2) ? "bi-star-fill" : "bi-star"}"></i>`;
  }
  return html + `<span class="numero">${rating.toFixed(1)}/10</span>`;
}

//esto es para preparar los datos de la película, ya que la API devuelve diferentes nombres de campos
//segun el endpoint, por lo que se normalizan para que la aplicación funcione correctamente
function preparar(pelicula) {
  return {
    id: pelicula.id || pelicula.tconst || pelicula.imdb_id,
    primaryTitle: pelicula.primaryTitle || pelicula.title || "Sin título",
    startYear: Number(pelicula.startYear || pelicula.year || 0),
    runtimeMinutes: pelicula.runtimeMinutes || pelicula.runtime || null,
    averageRating: Number(pelicula.averageRating || pelicula.rating || 0),
    numVotes: Number(pelicula.numVotes || pelicula.votes || 0),
    primaryImage: pelicula.primaryImage?.url || pelicula.primaryImage || pelicula.image || placeholder(),
    genres: pelicula.genres || [],
    description: pelicula.description || pelicula.plot || "Sin sinopsis disponible.",
    spokenLanguages: pelicula.spokenLanguages || pelicula.languages || [],
    countriesOfOrigin: pelicula.countriesOfOrigin || pelicula.countries || [],
    interests: pelicula.interests || [],
    budget: pelicula.budget || null,
    grossWorldwide: pelicula.grossWorldwide || pelicula.boxOffice || null,
    contentRating: pelicula.contentRating || "No disponible",
    productionCompanies: pelicula.productionCompanies || [],
    externalLinks: pelicula.externalLinks || [],
    trailer: pelicula.trailer || pelicula.trailerUrl || "",
    metascore: pelicula.metascore || pelicula.metaScore || 0
  };
}
//Esta función toma los datos de la API y los prepara para que la aplicación funcione correctamente
function datos(data) {
  return (data.results || data).map(preparar);
}

function cargarLocal() {
  $.getJSON("peliculas.json")
    .done(data => { peliculas = datos(data); mostrarPeliculas(); })
    .fail(mostrarError);
}

function cargarPeliculas() {
  $("#cargando").removeClass("d-none");
  $("#error").addClass("d-none");
  $.ajax(settings)
    .done(data => { peliculas = datos(data); mostrarPeliculas(); })
    .fail(cargarLocal);
}

//llama a #busqueda que es un keyup y filtra las peliculas por el valor de busqueda y el rango de años seleccionado
function mostrarPeliculas() {
  const busqueda = String($("#busqueda").val() || "").toLowerCase();
  const resultado = peliculas.filter(pelicula =>
    pelicula.primaryTitle.toLowerCase().includes(busqueda) &&
    pelicula.startYear >= desde && pelicula.startYear <= hasta
  );

  $("#cargando").addClass("d-none");
  $("#peliculas").empty();

  if (!resultado.length) {
    $("#peliculas").html('<div class="col-12 text-center text-muted py-5">No se encontraron películas.</div>');
    return;
  }

//para cada una de las peliculas filtradas, se crea un card con la información de la película y se agrega al contenedor #peliculas
  $.each(resultado, (_, pelicula) => {
    const favorito = esFavorito(pelicula.id);
    $("#peliculas").append(`
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card movie-card shadow-sm border-0">
          <img src="${pelicula.primaryImage}" onerror="this.src='${placeholder()}'" class="card-img-top poster" alt="${pelicula.primaryTitle}">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between gap-2">
              <h5 class="card-title fw-bold">${pelicula.primaryTitle}</h5>
              <button class="btn btn-link p-0 favorite-btn ${favorito ? "favorite-active" : ""}" data-id="${pelicula.id}"><i class="bi ${favorito ? "bi-heart-fill" : "bi-heart"} fs-4"></i></button>
            </div>
            <p class="text-muted mb-2">${pelicula.startYear || "Año desconocido"}</p>
            <div class="rating mb-3">${estrellas(pelicula.averageRating)}</div>
            <a href="reseña.html?id=${encodeURIComponent(pelicula.id)}" class="btn btn-dark mt-auto">Ver reseña</a>
          </div>
        </div>
      </div>`);
  });

  $(".movie-card").hover(
    function() { $(this).addClass("shadow").removeClass("shadow-sm"); },
    function() { $(this).addClass("shadow-sm").removeClass("shadow"); }
  );
}

function cambiarFavorito(id) {
  const favoritos = listaFavoritos();
  const posicion = favoritos.findIndex(pelicula => pelicula.id === id);
  const pelicula = peliculas.find(item => item.id === id) || peliculaActual;

  if (posicion >= 0) favoritos.splice(posicion, 1);
  else if (pelicula) favoritos.push(pelicula);

  guardarFavoritos(favoritos);
  actualizarBotonDetalle(id);
  if ($("#peliculas").length) mostrarPeliculas();
}

function actualizarBotonDetalle(id) {
  const favorito = esFavorito(id);
  $("#btnFavoritoDetalle")
    .toggleClass("favorite-active", favorito)
    .html(`<i class="bi ${favorito ? "bi-heart-fill" : "bi-heart"}"></i> Favorito`);
}

function mostrarFavoritos() {
  const favoritos = listaFavoritos();
  $("#listaFavoritos").empty();
  $("#sinFavoritos").toggleClass("d-none", favoritos.length > 0);
  $("#eliminarTodos").prop("disabled", favoritos.length === 0);

  $.each(favoritos, (_, pelicula) => {
    $("#listaFavoritos").append(`
      <div class="col-12 col-md-6"><div class="card border-0 shadow-sm"><div class="row g-0">
        <div class="col-4"><img src="${pelicula.primaryImage}" class="img-fluid rounded-start h-100 modal-poster" alt="${pelicula.primaryTitle}"></div>
        <div class="col-8"><div class="card-body"><h6 class="fw-bold">${pelicula.primaryTitle}</h6>
          <small>${pelicula.startYear} · ⭐ ${pelicula.averageRating}</small><div class="mt-2">
          <a class="btn btn-sm btn-dark" href="reseña.html?id=${encodeURIComponent(pelicula.id)}">Ver</a>
          <button class="btn btn-sm btn-outline-danger eliminar-favorito" data-id="${pelicula.id}">X</button>
        </div></div></div>
      </div></div></div>`);
  });
}

function mostrarError() {
  $("#cargando").addClass("d-none");
  $("#error").removeClass("d-none");
}

function cargarDetalle() {
  const id = new URLSearchParams(location.search).get("id");
  if (!id) return mostrarError();

  $("#cargando").removeClass("d-none");
  $.ajax(settings)
    .done(data => buscarDetalle(datos(data), id))
    .fail(() => $.getJSON("peliculas.json").done(data => buscarDetalle(datos(data), id)).fail(mostrarError));
}

function buscarDetalle(lista, id) {
  peliculaActual = lista.find(pelicula => pelicula.id === id);
  peliculaActual ? pintarDetalle(peliculaActual) : mostrarError();
}
//Esta función toma los datos de la película y los muestra en la página de detalle
function pintarDetalle(pelicula) {
  $("#cargando").addClass("d-none");
  $("#detalle").removeClass("d-none").hide().fadeIn(500);
  $("#imagen").attr({ src: pelicula.primaryImage, alt: pelicula.primaryTitle });
  $("#titulo").text(pelicula.primaryTitle);
  $("#anio").text(pelicula.startYear || "Año no disponible");
  $("#duracion").text(pelicula.runtimeMinutes ? pelicula.runtimeMinutes + " minutos" : "Duración no disponible");
  $("#rating").html(estrellas(pelicula.averageRating) + ` <small class="text-muted">(${pelicula.numVotes.toLocaleString()} votos)</small>`);
  $("#generos").html(pelicula.genres.map(item => `<span class="badge badge-genero">${typeof item === "string" ? item : item.name}</span>`).join("") || "No disponible");
  $("#descripcion").text(pelicula.description);
  $("#intereses").html(pelicula.interests.map(item => `<span class="badge badge-genero">${typeof item === "string" ? item : item.name}</span>`).join("") || "No disponible");
  $("#trailer").toggle(!!pelicula.trailer).attr("href", pelicula.trailer || "#");
  $("#enlaces").html(pelicula.externalLinks.map(item => `<a target="_blank" rel="noopener" class="d-block" href="${item.url || item}">${item.label || item.title || item.url || item}</a>`).join("") || "No disponible");
  $("#idiomas").text(texto(pelicula.spokenLanguages));
  $("#paises").text(texto(pelicula.countriesOfOrigin));
  $("#clasificacion").text(pelicula.contentRating);
  $("#presupuesto").text(dinero(pelicula.budget));
  $("#recaudacion").text(dinero(pelicula.grossWorldwide));
  $("#productoras").text(texto(pelicula.productionCompanies));
  const meta = Number(pelicula.metascore) || 0;
  $("#metaNumero").text(meta ? meta + "/100" : "No disponible");
  $("#metascore").css("width", Math.min(meta, 100) + "%").removeClass("bg-success bg-warning bg-danger").addClass(meta >= 70 ? "bg-success" : meta >= 50 ? "bg-warning" : "bg-danger");
  actualizarBotonDetalle(pelicula.id);
  $("#btnFavoritoDetalle").data("id", pelicula.id);
}

//Cuando el documento está listo, se inicializan los eventos y se cargan las películas o el detalle según corresponda
$(document).ready(function() {
  $("#contadorFavoritos").text(listaFavoritos().length);
  if ($("#peliculas").length) cargarPeliculas();
  if ($("#detalle").length) cargarDetalle();

  $("#busqueda").keyup(mostrarPeliculas);
  $("#reintentar").click(cargarPeliculas);
  $(".filtro").click(function() {
    $(".filtro").removeClass("active");
    $(this).addClass("active");
    desde = Number($(this).data("desde"));
    hasta = Number($(this).data("hasta"));
    mostrarPeliculas();
  });
  $(document).on("click", ".favorite-btn", function() { cambiarFavorito($(this).data("id")); });
  $("#btnFavoritoDetalle").click(function() { cambiarFavorito($(this).data("id")); });
  $("#btnFavoritos").click(function() { mostrarFavoritos(); new bootstrap.Modal("#modalFavoritos").show(); });
  $(document).on("click", ".eliminar-favorito", function() { cambiarFavorito($(this).data("id")); mostrarFavoritos(); });
  $("#eliminarTodos").click(function() { guardarFavoritos([]); mostrarFavoritos(); if ($("#peliculas").length) mostrarPeliculas(); });
});


//Y pues ya, la verdad no hay mucho que ver algunas cosas si la hice yo, otras las busqué y otras las hizo la IA, pero me pareció buena idea ponerlas aquí.
//Sobre todo la IA para cosas que no sabía como se hacían, como el tema de las estrellas y el rating y ayudarme a hacer debug. 