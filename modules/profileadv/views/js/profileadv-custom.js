/**
 * 2011 - 2019 StorePrestaModules SPM LLC.
 *
 * MODULE profileadv
 *
 * @author    SPM <spm.presto@gmail.com>
 * @copyright Copyright (c) permanent, SPM
 * @license   Addons PrestaShop license limitation
 * @version   1.2.9
 * @link      http://addons.prestashop.com/en/2_community-developer?contributor=790166
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 */

function profileadv_change(id) {
	if (id == 1) {
		$('#profileadv_btn_cancel').show();
		$('#profileadv_edit_button').hide();
		$('#profileadvimg').show()
		$('.fakeButton').show();
		$('#nameprofileadvimg').show();
		$('#uniform-profileadvimg').show();
	} else {
		$('#profileadv_btn_cancel').hide();
		$('#profileadv_edit_button').show();
		$('#profileadvimg').hide()
		$('.fakeButton').hide();
		$('#nameprofileadvimg').hide();
		$('#uniform-profileadvimg').hide();

	}
}

$(document).ready(function () {

	//Sections
	if ($('.navigation-buttons').length > 0) {
		$('.navigation-buttons button').click(function (e) {

			//Show section
			step = $(this).data("step");

			//Validate steps before continue
			if ($(this).hasClass('next')) {
				let current = $(this).parents('section').data('step');

				if (validateStep(current)) {
					//Hide all elements
					$('#add-pet-form section').fadeOut(1);

					if ($('#add-pet-form section[data-step="' + step + '"]').length > 0) {
						$('#add-pet-form section[data-step="' + step + '"]').fadeIn(200);
					}
				};
			} else {
				$('#add-pet-form section').fadeOut(1);
				$('#add-pet-form section[data-step="' + step + '"]').fadeIn(200);
			}
		});
	}
	if ($('.inputType').length > 0) {
		$('.inputType').on('click', function () {
			$('select[id*="Breed"]').css("display", "none").attr('data-breedselected', 'false');
			if ($(this).data("value") === 1) {
				$('#inputDogBreed').css("display", "block").attr('data-breedselected', 'true');
				$('.physical-image').each(function () {
					var src = $(this).attr('src');
					$(this).attr('src', src.replace('cat', 'dog'));
				});
			} else if ($(this).data("value") === 2) {
				$('#inputCatBreed').css("display", "block").attr('data-breedselected', 'true');
				$('.physical-image').each(function () {
					var src = $(this).attr('src');
					$(this).attr('src', src.replace('dog', 'cat'));
				});
			}

			var val = $(this).data('value');

			//Delete active class for all elements
			$(this).siblings('img').removeClass('active-button');

			//Add active class to this button
			$(this).addClass('active-button');

			//Set value to input
			$(this).siblings('input').val(val);

			//Go to step 2
			$('#add-pet-form section[data-step="1"]').fadeOut(1);
			$('#add-pet-form section[data-step="2"]').fadeIn(200);

		});

		/* Hover img */
		$('.inputType').mouseenter(function () {
			var src = $(this).attr('src');
			if (src.indexOf("hover") <= 0) {
				$(this).attr('src', src.replace('.png', '-hover.png'));
			}
		});

		$('.inputType').mouseleave(function () {
			var src = $(this).attr('src');
			$(this).attr('src', src.replace('-hover.png', '.png'));
		});
	}

	if ($('.inputGenre').length > 0) {
		$('.inputGenre').on('click', function () {
			var val = $(this).data('value');

			//Delete active class for all elements
			$('.inputGenre').removeClass('active-button');

			//Add active class to this button
			$(this).addClass('active-button');

			//Set value to input
			$('#inputGenre').val(val);
		});
	}

	if ($('select[id*="Breed"]').length > 0) {
		$('#inputType').on('click', function () {
			$('select[id*="Breed"]').css("display", "none").attr('data-breedselected', 'false');
			if (this.value === '1') {
				$('#inputDogBreed').css("display", "block").attr('data-breedselected', 'true');
			} else if (this.value === '2') {
				$('#inputCatBreed').css("display", "block").attr('data-breedselected', 'true');
			}
		});
	}

	if ($('.activity-cards').length > 0) {
		$('.activity-cards .card').on('click', function () {

			var val = $(this).data('value');

			//Delete active class for all elements
			$('.activity-cards .card').removeClass('active-card');

			//Add not selected class
			$('.activity-cards .card').addClass('non-active-card');

			//Add active class to this button
			$(this).removeClass('non-active-card');

			//Set value to input
			$('#inputActivity').val(val);

			// Remove error border when activity is selected
			$('.activity-cards').css('border', 'none');

		});
	}

	/*if ($('.physical-range').length > 0) {
		$('.physical-range').on('input', function () {
			$('.physical-image').fadeTo(100, 0.1, function () {

				var val = $('#inputPhyisicalCondition').val();
				var type = $('#inputType').val();
				var src = $('.physical-image').attr('src');
				if (type === '1') {
					$('.physical-image').attr('src', src.replace('cat', 'dog').replace(/[0-9]+/, val));
				} else if (type === '2') {
					$('.physical-image').attr('src', src.replace('dog', 'cat').replace(/[0-9]+/, val));
				}

			}).fadeTo(100, 1);
		});
	}*/

	if ($('.select-button').length > 0) {
		$('.select-button button').click(function (e) {
			var val = $(this).data('value');

			//Delete active class for all elements
			$(this).siblings('button').removeClass('active-button');

			//Add active class to this button
			$(this).addClass('active-button');

			//Set value to input
			$(this).siblings('input').val(val);
		});
	}

	if ($('.enable-option-button').length > 0) {
		$('.enable-option-button button').click(function (e) {
			var val = $(this).data('value');

			//Delete active class for all elements
			$(this).siblings('button').removeClass('active-button');

			//Add active class to this button
			$(this).addClass('active-button');

			//Set value to input
			$(this).siblings('input').val(val);

			if (val == '1') {
				$(this).parent('.enable-option-button').siblings('.options-list').fadeIn(200);
			} else {
				$(this).parent('.enable-option-button').siblings('.options-list').fadeOut(200);
			}
		});
	}

	if ($('.pet-feeding').length > 0) {
		$('.pet-feeding .img-feeding').click(function (e) {

			//Delete active class for all elements
			$(this).parents('.pet-feeding').find('img').removeClass('active-button');
			$(this).parents('.pet-feeding').find('img').each(function () {
				if (!$(this).hasClass('active-button')) {
					var src = $(this).attr('src');
					$(this).attr('src', src.replace('-hover.png', '.png'));
				}
			});

			//Add active class to this button
			$('.pet-feeding .img-feeding').addClass('non-active-button');
			$(this).removeClass('non-active-button');
			$(this).addClass('active-button');

			//Set hover image
			var src = $(this).attr('src');
			$(this).attr('src', src.replace('.png', '-hover.png'));

			var val = $(this).data('value');
			$(this).parent('p').siblings('input').val(val);

			// Remove error border when feeding is selected
			$('.pet-feeding').css('border', 'none');
		});

		$('.pet-feeding .img-feeding').mouseenter(function () {
			var src = $(this).attr('src');
			if (src.indexOf("hover") <= 0) {
				$(this).attr('src', src.replace('.png', '-hover.png'));
			}
		});

		$('.pet-feeding .img-feeding').mouseleave(function () {
			if (!$(this).hasClass('active-button')) {
				var src = $(this).attr('src');
				$(this).attr('src', src.replace('-hover.png', '.png'));
			}
		});
	}

	if ($('#add-pet-form #inputName').length > 0) {
		$('#inputName').on('keyup', function () {
			$('.pet-name-span').text($(this).val());
		});
	}
	$('[id^=collapse]').on('show.bs.collapse', function () {
		$('.collapse').removeClass("in");
		$(this).addClass("in");
	})

	if ($('#module-profileadv-petlist').length > 0) {
		$(function () {
			$('[data-toggle="tooltip"]').tooltip()
		})
	}

	if ($('#inputWeight').length > 0) {
		$('#inputWeight').on('change', function () {
			let weight = $(this).val();
			$('#inputDesiredWeight').val(weight);
		});
	}

	if ($('#desired-weight-options').length > 0) {
		$('#desired-weight-options button').on('click', function () {

			if (!$('#inputWeight').val() || isNaN($('#inputWeight').val()) || parseInt($('#inputWeight').val()) > 90 || parseInt($('#inputWeight').val()) < 0) {
				$('#desired-weight-row').removeClass('hidden');
				$('#inputWeight').css('border', '1px solid red');
				return false;
			} else {
				let val = parseInt($(this).data("value"));

				if (val === 3) {
					$('#desired-weight-row').addClass('hidden');
					$('.desired-weight-row').addClass('hidden');
				} else {
					$('#desired-weight-row').removeClass('hidden');
					$('.desired-weight-row').removeClass('hidden');
				}
			}
		})
	}

	if ($('.inputGenre').length) {
		$('.inputGenre').on('click', function () {
			let src = $('.inputGenre[data-value="1"]').attr('src');
			$('.inputGenre[data-value="1"]').attr('src', src.replace('male_selected', 'male'));
			src = $('.inputGenre[data-value="2"]').attr('src');
			$('.inputGenre[data-value="2"]').attr('src', src.replace('male_selected', 'male'));

			src = $(this).attr('src');
			if (parseInt($(this).data('value')) === 1) {
				if (src.indexOf('selected') === -1) {
					$(this).attr('src', src.replace('male', 'male_selected'));
				}
			} else {
				if (src.indexOf('selected') === -1) {
					$(this).attr('src', src.replace('female', 'female_selected'));
				}
			}
		});
	}

	$('#carousel').flickity({
		// options
		cellAlign: 'center',
		contain: true,
		draggable: false,
		pageDots: false,
		wrapAround: true,
		autoPlay: 1500,
		pauseAutoPlayOnHover: true,
		lazyLoad: true
	});

	//Autoadd recommended product to cart
	if ($('#module-profileadv-addfirstpet #add-to-cart-or-refresh').length > 0) {
		$(function () {
			$('#add-to-cart-or-refresh button').trigger("click");
		})
	}

});

window.onload = function () {
	if ($('#product #mipets-product .monthly-amount-popover').length > 0) {
		$(function () {
			$('.monthly-amount-popover').popover({
				container: 'body'
			})
		})
	}
}

function submitClicked() {
	setTimeout(function () {
		$('#add-pet-form').css('background', 'transparent');
		$('#add-pet-form').html('<div class="text-center"><div><img class="img-fluid inputType"src="/modules/profileadv/views/img/wizard/loader.gif?v=2023062701" alt="loader"></div></div>');
	}, 100);

	$('form#user_profile_photo').submit();
}

function validateStep(step) {

	switch (step) {
		case 2:
			$('#inputName').css('border', '1px solid #00B4DD');
			$('.inputGenre').css('border', '1px solid #FFF');
			$('.pet-exists').css('display', 'none');

			if (!$('#inputName').val()) {
				$('#inputName').css('border', '1px solid red');
				return false;
			}
			// if (duplicatedPet($('#inputName').val())) {
			// 	$('.pet-exists').css('display', 'block');
			// 	return false;
			// }
			if (!$('#inputGenre').val()) {
				$('.inputGenre').css('border', '1px solid red');
				return false;
			}

			return true;
		case 3:
			$('#inputBirth').css('border', '1px solid #00B4DD');
			$('#inputBirth').next('.birth-error').remove();

			var birthVal = $('#inputBirth').val();
			var errorMsg = '';
			if (!birthVal) {
				errorMsg = 'Por favor, introduce una fecha de nacimiento!';
			} else {
				var birthDate = new Date(birthVal);
				var now = new Date();
				var minDate = new Date(now.getFullYear() - 35, now.getMonth(), now.getDate());
				if (birthDate < minDate) {
					errorMsg = 'La fecha de nacimiento no puede ser mayor a 35 años.';
				} else if (birthDate > now) {
					errorMsg = 'La fecha de nacimiento no puede mayor a la fecha actual.';
				}
			}
			if (errorMsg) {
				$('#inputBirth').css('border', '1px solid red');
				$('<div class="birth-error" style="color:red;font-size:0.9em;margin-top:4px;">' + errorMsg + '</div>').insertAfter('#inputBirth');
				return false;
			}

			return true;
		case 4:
			$('#inputWeight').css('border', '1px solid #00B4DD');
			$('#inputDesiredWeight').css('border', '1px solid #00B4DD');

			if (!$('#inputWeight').val() || isNaN($('#inputWeight').val()) || parseInt($('#inputWeight').val()) > 90 || parseInt($('#inputWeight').val()) < 0 || $('#inputWeight').val() === "") {
				$('#inputWeight').css('border', '1px solid red');
				return false;
			}

			if (!$('#inputDesiredWeight').val() || isNaN($('#inputDesiredWeight').val()) || parseInt($('#inputDesiredWeight').val()) > 90 || parseInt($('#inputDesiredWeight').val()) < 0 || $('#inputDesiredWeight').val() === "") {
				$('#inputDesiredWeight').css('border', '1px solid red');
				return false;
			}
			return true;
		case 5:
			$('.activity-cards').css('border', 'none');
			$('.pet-feeding').css('border', 'none');

			var hasErrors = false;

			if (!$('#inputActivity').val()) {
				$('.activity-cards').css('border', '2px solid red');
				hasErrors = true;
			}

			if (!$('#inputFeeding').val()) {
				$('.pet-feeding').css('border', '2px solid red');
				// Animation effect for feeding selection
				$('.pet-feeding img').css('transform', 'scale(0.8)');
				setTimeout(function () {
					$('.pet-feeding img').css('transform', 'scale(1)');
				}, 1000);
				hasErrors = true;
			}

			if (hasErrors) {
				return false;
			}

			return true;
		case 6:
			return true;
		case 7:
			return true;
		case 8:
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (emailRegex.test($('#inputEmail').val())) {
				submitClicked();
			} else {
				$('#inputEmail').css('border', '1px solid red');
				return false;
			}
	}
}

/**
 * Check if pet name is duplicated
 * @param {string} name 
 */
function isDuplicatedPet(name) {
	let customer_pet_list = $('#petcustomerlist').data('petlist');

	if (customer_pet_list !== 0) {
		customer_pet_list = customer_pet_list.toLowerCase();

		const haveOverlap = (string1, string2) => findOverlap(string1, string2).length >= 3;

		if (haveOverlap(customer_pet_list, name.toLowerCase())) {
			return true;
		}
	}

	return false;
}

function findOverlap(a, b) {
	if (b.length === 0) {
		return '';
	}

	if (a.endsWith(b)) {
		return b;
	}

	if (a.indexOf(b) >= 0) {
		return b;
	}

	return findOverlap(a, b.substring(0, b.length - 1));
}