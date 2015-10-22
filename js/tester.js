$(document).ready(function(){
	$('#submitForm').click(function(){
		var type = $('input[name=type]:checked', '#radioDiv').val();
		var endpoint = 'api/index.php/' + $('#endpoint').val();
		var body = $('#body').val();
		try{
	        body=JSON.parse(body);
	    }catch(e){
	        $('#result').html("Malformed JSON, could not be parsed");
	        return false;
	    }
		if(type == 0){
			$.ajax({
				url: endpoint,
				type: "get",
				dataType: "JSON",
				data: body, 
				success: function(result){
					$('#result').text(JSON.stringify(result));
				},
				error: function(){
					$('#result').html("Could not complete http request as entered.<br>Endpoint: " + endpoint + "<br>Body: " + JSON.stringify(body));
				}
			});
		}
		else{
			$.ajax({
				url: endpoint,
				type: "post",
				dataType: "JSON",
				data: body, 
				success: function(result){
					$('#result').text(JSON.stringify(result));
				},
				error: function(xhr, status, error){
					$('#result').html("Could not complete http request as entered.<br>Endpoint: " + endpoint + "<br>Body: " + JSON.stringify(body));
				}
			});
		}
	});
});