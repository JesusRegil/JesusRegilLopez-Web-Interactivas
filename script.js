//Carrito en memoria como un arreglo de objetos:

// { id, nombre, precio, img, cantidad }

let carrito = [];

 

const listaCarro = document.querySelector("#lista-carro tbody");

const totalCarrito = document.querySelector("#carrito-total");

const botonesAgregar = document.querySelectorAll(".agregar-al-carrito");

const botonVaciar = document.querySelector("#vaciar-carrito");

const menuToggle = document.querySelector("#menu-toggle");

const navBar = document.querySelector(".nav-bar");

 

// Al hacer click en "Agregar al carrito" (tanto productos estrella como a granel)

botonesAgregar.forEach((boton) => {

    boton.addEventListener("click", (e) => {

        e.preventDefault(); // evita que el href="#" recargue/salte de posición

 

        const producto = {

            id: boton.dataset.id,

            nombre: boton.dataset.nombre,

            precio: parseFloat(boton.dataset.precio),

            img: boton.dataset.img,

        };

 

        agregarAlCarrito(producto);

    });

});

 

function agregarAlCarrito(producto) {

    const existente = carrito.find((item) => item.id === producto.id);

 

    if (existente) {

        existente.cantidad += 1; // si ya está en el carrito, solo sube la cantidad

    } else {

        carrito.push({ ...producto, cantidad: 1 });

    }

 

    actualizarCarrito();

}

 

function actualizarCarrito() {

    listaCarro.innerHTML = ""; //limpiar carro

 

    let total = 0;

 

    carrito.forEach((item) => {

        const fila = document.createElement("tr");

        const subtotal = item.precio * item.cantidad;

        total += subtotal;

 

        fila.innerHTML = `

            <td><img src="${item.img}" alt="${item.nombre}"></td>

            <td>${item.nombre}</td>

            <td>$${item.precio.toFixed(2)}</td>

            <td>${item.cantidad}</td>

        `;

 

        listaCarro.appendChild(fila);

    });

 

    totalCarrito.textContent = `Total: $${total.toFixed(2)}`;

}

 

// Vaciar carrito

if (botonVaciar) {

    botonVaciar.addEventListener("click", (e) => {

        e.preventDefault();

        carrito = [];

        actualizarCarrito();

    });

}

 

if (navBar) {

    navBar.querySelectorAll("a").forEach((link) => {

        link.addEventListener("click", () => {

            if (menuToggle) {

                menuToggle.checked = false;

            }

        });

    });

}