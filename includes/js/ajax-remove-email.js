jQuery(function($){

	var deleteBtn = $('.delete'),
		modal = $('#confirm-unsubscribe-email'),
		confirmDeleteBtn = $('.button[data-action="mc-unsubscribe"]'),
		tableContainer = $('#temporary-subscribers'),
		email,
		nonce,
		link;

	confirmDeleteBtn.on('click', function(e) {

		e.preventDefault();

		nonce = $(this).attr("data-nonce");
		email = $(this).attr("data-email");

		modal.foundation('close');

		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : ajax_object.ajax_url,
			data : {
				action: "cit_mc_unsubscribe",
				email : email,
				nonce: nonce
			},
			beforeSend : function(){
				// console.log('About to Send: nonce = '+nonce+'; email = '+email);
			},
			success: function(response) {
	            if(response.type == "success") {
               		tableContainer.html(response.message+response.replace);
            	} else {
            		alert("No se pudo eliminar el suscriptor.");
            	}
            },
            error: function(jqXHR, textStatus, errorThrown) {
            	alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
        });
	});

	deleteBtn.on('click', function(e) {
		
		e.preventDefault();
		
		email = $(this).data('email');
		nonce = $(this).data('nonce');

		modal.find('.confirm-email').html(email);

		link = ajax_object.ajax_url + '?action=cit_mc_unsubscribe&email=' + encodeURIComponent(email) + '&nonce=' + nonce;

		// link = admin_url('admin-ajax.php?action=mc_unsubscribe&email='.urlencode($member['email']).'&nonce='.$nonce);

		modal.find('.button.confirm-unsubscribe').attr('href', link).attr('data-email', email).attr('data-nonce', nonce);

		// alert(modal.find('.button.confirm-unsubscribe').data('action'));
	});

    //- Function to bind handlers to the relevant Foundation events
    function bindRevealEvents() {
        $(window).on(
            'closeme.zf.reveal', function () {
                // alert("'closeAll.zf.Reveal' fired.");
            }
        );

        $(window).on(
            'closed.zf.reveal', function () {
                // alert("'closeAll.zf.Reveal' fired.");
            }
        );

        $(window).on(
            'open.zf.reveal', function () {
                // alert("'open.zf.Reveal' fired.");
            }
        );
    }


	bindRevealEvents();

});