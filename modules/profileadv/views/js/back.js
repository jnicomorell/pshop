
function deletePet(reference, customer) {
    if (confirm("¿Estás seguro que deseas eliminar a esta mascota?")) {
        $.ajax({
            type: "POST",
            url: "/module/profileadv/petlist?action=delete",
            data: {
                reference: reference,
                id_customer: parseInt(customer),
                source: 'back'
            },
            beforeSend: function () {
                $('.delete-pet').css('visibility', 'hidden');;
            },
            success: function (response) {
                location.reload()
            }
        });
    }
}

function notifyAmountToCustomer() {

    var customer = parseInt($('#pet-customer').val());
    var customer_name = $('#pet-customer-name').val();
    var customer_phone = $('#pet-customer-phone').val();
    var customer_risk = $('#pet-customer-risk').val();
    var employee = parseInt($('#pet-employee').val());
    var employee_name = $('#pet-employee-name').val();
    var pet = $('#pet-name').val();
    var current_amount = parseInt($('#pet-amount').val());
    var prev_amount = parseInt($('#pet-prev-amount').val());

    //customer = 1 --> news pets without id_customer associated

    if ((current_amount !== prev_amount) && customer_risk < 4 && customer > 1) {
        Swal.fire({
            title: "¿Quieres enviar un WhatsApp al cliente para notificar la nueva cantidad diaria que debe comer la mascota?",
            showDenyButton: true,
            confirmButtonText: "Aceptar",
            denyButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                let valid_whatsapp = isValidWhatsapp(customer_phone, employee);

                if (valid_whatsapp) {
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        dataType: 'json',
                        url: '/modules/profileadv/controllers/admin/whatsapp_api.php',
                        async: true,
                        data: {
                            customer: customer,
                            customer_name: customer_name,
                            customer_phone: customer_phone,
                            customer_predefined_message: 1,
                            employee_id: employee,
                            employee_name: employee_name,
                            pet: pet,
                            pet_prev_amount: prev_amount,
                            pet_current_amount: current_amount,
                            token: 'y42eotpy543a',
                            action: 'send-amount-text'
                        },
                        success: function (res) {

                            if (res.sent) {
                                $.growl.notice({
                                    message: "WhatsApp enviado correctamente"
                                });
                                $('#pet_edit_data').submit();
                            } else {
                                $.growl.error({
                                    message: "Ha ocurrido un error al enviar el mensaje. Contacta con informática"
                                });
                            }
                        }
                    })
                } else {
                    $.growl.error({
                        message: "Error al enviar el WhatsApp, contacto no válido"
                    });
                }
            } else {
                $('#pet_edit_data').submit();
            }
        });
    } else {
        $('#pet_edit_data').submit();
    }
}

function isValidWhatsapp(phone, employee) {
    let result;
    $.ajax({
        type: 'POST',
        cache: false,
        dataType: 'json',
        url: '/modules/profileadv/controllers/admin/whatsapp_api.php',
        async: false,
        data: {
            customer_phone: phone,
            employee_id: employee,
            token: 'y42eotpy543a',
            action: 'check-whatsapp'
        },
        success: function (res) {
            result = res['status'] === 'valid' ? true : false;
        }
    });

    return result;
}

function ShowBreedList() {
    if ($('select[id*="Breed"]').length > 0) {

        $('select[id*="Breed"]').css("display", "none").attr('data-breedselected', 'false');
        if ($('#inputType').val() === '1') {
            $('#inputDogBreed').css("display", "block").attr('data-breedselected', 'true');
        } else if ($('#inputType').val() === '2') {
            $('#inputCatBreed').css("display", "block").attr('data-breedselected', 'true');
        }
    }
};

$(document).ready(function () {
    if ($('.adminprofileadvlist').length > 0) {
        new DataTable('.table');
    }

    if ($('.adminprofileadv').length > 0) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    $('#pet-amount-blocked').change(function () {
        if (this.checked) {
            $('#pet-amount').prop('readonly', true);
        } else {
            $('#pet-amount').prop('readonly', false);
        }
    });
});

function updateSendedEmail(ref, value) {
    $.ajax({
        type: 'POST',
        cache: false,
        dataType: 'json',
        url: '/modules/profileadv/controllers/admin/ajax.php',
        async: false,
        data: {
            reference: ref,
            email_sended: parseInt(value),
            action: 1,
        },
        success: function (res) {
            if (res) {
                showSuccessMessage(update_success_msg);
            } else {
                $.growl.error({
                    message: "Ha ocurrido un error al actualizar los datos. Por favor, contacta con soporte"
                });
            }
        }
    });
}