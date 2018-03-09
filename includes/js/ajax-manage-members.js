jQuery(function($){

	var modal,
		email,
		username,
		user,
		nonce,
		link,
		editMemberBtn = $('.edit-member'),
		deleteMemberBtn = $('.delete-member'),
		confirmDeleteMemberBtn = $('.button.confirm-delete-member'),
		approveTicky = $( 'input[name="user-is-approved"]' ),
		confirmApproveBtn =  $('.button.confirm-approve-member'),
		mcUnsubscribeBtn = $('.unsubscribe'),
		confirmMcUnsubscribeBtn = $('.button[data-action="confirm-mc-unsubscribe"]'),
		tableContainer = $('#temporary-subscribers');





	/*
	 * Approve/unapprove members
	 */
	approveTicky.change(function() {

		username = $(this).data('username');
		nonce = $(this).data('nonce');

		modal = $( '#' + $(this).data('open') );
		modal.foundation('open');

		link = ajax_object.ajax_url + '?action=cit_approve_member&username=' + username + '&nonce=' + nonce;


	    // CHECK
	    if($(this).is(':checked')) {
	    	// alert('checked ' + $(this).val());
	    	modal.find('.confirm-ask').html('¿Está seguro que quiere aprobar <strong>'+$(this).data('nickname')+'</strong>?');

	    	confirmApproveBtn.attr('data-approve', true).html('Sí, aprobarlo');

	    // UNCHECK
	    } else {
	    	// alert('UNchecked ' + $(this).val());
	    	modal.find('.confirm-ask').html('¿Está seguro que quiere revocar aprobación de <strong>'+$(this).data('nickname')+'</strong>?');

	    	confirmApproveBtn.attr('data-approve', false).html('Sí, revocar');
	    }

	    link = ajax_object.ajax_url + '?action=cit_approve_member&username=' + username + '&nonce=' + nonce;

	    confirmApproveBtn.attr('href', link).attr('data-username', username).attr('data-nonce', nonce);

	});


	confirmApproveBtn.on('click', function(e) {

		e.preventDefault();

		console.log('Clicked on confirm approve');

		nonce = $(this).attr("data-nonce");
		username = $(this).attr("data-username");
		approve = $(this).attr("data-approve");

		//convert to boolean
		approve = (approve == 'true');

		console.log('1. approve is type '+jQuery.type( approve ));

		tableContainer = $('#premium-members-table');

		modal.foundation('close');

		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : ajax_object.ajax_url,
			data : {
				action: "cit_approve_member",
				username : username,
				approve : approve,
				nonce: nonce
			},
			beforeSend : function(){
				console.log('APPROVE MEMBER: About to send: nonce = '+nonce+'; username = '+username+ ' approve = '+approve);
				console.log('2. approve is type '+jQuery.type( approve ));
			},
			success: function(response) {
	            console.log('GO TO SUCCESS');
	            console.log('3. approve is type '+jQuery.type( approve ));
	            if(response.type == "success") {
	            	tableContainer.prepend(response.message);
               		// tableContainer.html(response.message+response.replace);
            	} else {
            		alert("No se pudo eliminar el afiliado.");
            	}
            },
            error: function(jqXHR, textStatus, errorThrown) {
            	console.log('GO TO ERROR');
            	alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
        });
	});



	/*
	 * Delete members
	 */

	deleteMemberBtn.on('click', function(e) {

		e.preventDefault();
		console.log('click on open delete member modal');

		username = $(this).data('username');
		nonce = $(this).data('nonce');

		modal = $( '#' + $(this).data('open') );

		modal.find('.nickname').html($(this).data('nickname'));

		link = ajax_object.ajax_url + '?action=cit_mc_delete_member&username=' + username + '&nonce=' + nonce;

		confirmDeleteMemberBtn.attr('href', link).attr('data-username', username).attr('data-nonce', nonce);
	});

	confirmDeleteMemberBtn.on('click', function(e) {

		e.preventDefault();

		nonce = $(this).attr("data-nonce");
		username = $(this).attr("data-username");

		tableContainer = $('#premium-members-table');

		modal.foundation('close');

		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : ajax_object.ajax_url,
			data : {
				action: "cit_mc_delete_member",
				username : username,
				nonce: nonce
			},
			beforeSend : function(){
				console.log('DELETE MEMBER: About to send: nonce = '+nonce+'; username = '+username);
			},
			success: function(response) {
	            console.log('GO TO SUCCESS');
	            if(response.type == "success") {
	            	tableContainer.prepend(response.message);
               		// tableContainer.html(response.message+response.replace);
            	} else {
            		alert("No se pudo eliminar el afiliado.");
            	}
            },
            error: function(jqXHR, textStatus, errorThrown) {
            	console.log('GO TO ERROR');
            	alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
        });
	});


	/*
	 * Unsubscribe temp members
	 */

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