$('.swal-confirm').click(function(e) {
    	e.preventDefault();
    	var swtitle = $(this).data('swtitle');
    	var swmessage = $(this).data('swmessage');
    	var form = $(this).parents(form);
	    swal({
	        title: swtitle,
	        text: swmessage,
	        type: "warning",
	        showCancelButton: true,
	        confirmButtonColor: '#DD6B55',
	        confirmButtonText: 'Yes, I am sure!',
	        cancelButtonText: "No, cancel!"
	    }).then(
		   	function(result) {
		   		swal("Good job!", "You clicked the button!", "success").then(
		   			setTimeout(function () {
					    form.submit();
					}, 1000)
		   		);
		  	},
		  	function(dismiss){
		  		return false;
		  	}
	    ).catch(swal.noop);
	});
