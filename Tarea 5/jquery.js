$(document).ready(function() {
    const cartModal = new bootstrap.Modal(document.getElementById('carritoModal'));
    const subModal = new bootstrap.Modal(document.getElementById('suscripcionModal'));
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    let totalPrice = 0;

    $('.btn-add-to-cart').on('click', function(e) {
        e.preventDefault();

        const product = $(this).data('product');
        const price = parseFloat($(this).data('price'));
        totalPrice += price;

        $('#modalProductName').text(product);
        $('#modalProductPrice').text('$' + price.toFixed(2));
        $('#modalProductTotal').text('$' + totalPrice.toFixed(2));
        
        cartModal.show();
    });

    $('.btn-vaciar').on('click', function() {
        totalPrice = 0;
        $('#modalProductTotal').text('$0.00');
        $('#modalProductName').text('Producto');
        $('#modalProductPrice').text('$0.00');
    });

    $('#btnSubscribe').on('click', function() {
        const email = $('#emailInput').val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && emailRegex.test(email)) {
            $('#modalEmailSubscription').text(email);
            subModal.show();
            $('#emailInput').val('');
        } else {
            errorModal.show();
        }
    });

    $('a[href^="#"]').on('click', function(e) {
        const targetId = $(this).attr('href');

        if (targetId.length > 1 && $(targetId).length) {
            e.preventDefault();

            $('html, body').animate({
                scrollTop: $(targetId).offset().top - 70
            }, 500);
        }
    });

    $('#buscadorProductos').keyup(function() {
        const busqueda = $(this).val().toLowerCase().trim();
        let visibles = 0;

        $('.product-item').each(function() {
            const etiquetas = String($(this).data('tags')).toLowerCase();
            const coincide = etiquetas.indexOf(busqueda) !== -1;

            $(this).toggle(coincide);

            if (coincide) {
                visibles++;
            }
        });

        $('#sinResultados').toggleClass('d-none', visibles > 0);
    });

}); 
