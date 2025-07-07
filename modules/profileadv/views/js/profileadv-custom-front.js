function showAllPetsRows() {
    $('#mipets-product tr.hidden-row').each(function () {
        if ($(this).hasClass('hidden')) {
            $(this).removeClass('hidden');
        } else {
            $(this).addClass('hidden');
        }
    });
}