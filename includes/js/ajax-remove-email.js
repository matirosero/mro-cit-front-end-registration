jQuery(function($){

	var modal,
	email,
		userID,
		user,
		nonce,
		link,
		deleteMemberBtn = $('.delete-member')
		confirmDeleteMemberBtn = $('.button.confirm-delete-member'),
		mcUnsubscribeBtn = $('.unsubscribe'),
		confirmMcUnsubscribeBtn = $('.button[data-action="confirm-mc-unsubscribe"]'),
		tableContainer = $('#temporary-subscribers');


	deleteMemberBtn.on('click', function(e) {

		e.preventDefault();
		console.log('click on open delete member modal');

		userID = $(this).data('id');
		nonce = $(this).data('nonce');

		modal = $( '#' + $(this).data('open') );

		modal.find('.user-name').html($(this).data('user'));

		link = ajax_object.ajax_url + '?action=cit_delete_member&id=' + userID + '&nonce=' + nonce;

		confirmDeleteMemberBtn.attr('href', link).attr('data-id', userID).attr('data-nonce', nonce);
	});


	mcUnsubscribeBtn.on('click', function(e) {

		e.preventDefault();
		console.log('click on unsubscribe temp modal');

		email = $(this).data('email');
		nonce = $(this).data('nonce');

		modal = $( '#' + $(this).data('open') );

		modal.find('.confirm-email-label').html(email);

		link = ajax_object.ajax_url + '?action=cit_mc_unsubscribe&email=' + encodeURIComponent(email) + '&nonce=' + nonce;

		modal.find('.button.confirm-unsubscribe').attr('href', link).attr('data-email', email).attr('data-nonce', nonce);
	});


	confirmMcUnsubscribeBtn.on('click', function(e) {

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