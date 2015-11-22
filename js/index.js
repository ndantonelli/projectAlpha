function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function checkCookie() {
    var user = getCookie("reciever");
    if (user != "") 
        return true;
    else 
    	return false;
}

$(document).ready(function(){
	if(checkCookie()){
		$('body').append("Hi there!<br><br>Your recipient is " + getCookie("reciever"));
	}
	else{
		$('body').append("<header>Please Select Your Name:</header><div id=\"giverRadio\"></div><br><header>Please enter your SMU id number:</header><input id=\"studId\" type=\"text\"/><br><br><input type=\"submit\" id=\"submitForm\">");
		$.ajax({
				url: "api/oastuff.php/user",
				type: "get",
				dataType: "JSON",
				success: function(result){
					for(var i = 0; i < result.users.length; i++){
						$('#giverRadio').append("<label><input type=\"radio\" name=\"giverName\" value=" + result.users[i].id + ">" + result.users[i].name + "</label><br>")
					}
				},
				error: function(){
				}
			});
		$('#submitForm').click(function(){
			var uid = $('input[name=giverName]:checked', '#giverRadio').val();
			var sid = $('#studId').val();
			if(uid == undefined){
				alert("Please select your name from the list");
				return;
			}
			if(sid.length < 8 || sid.length > 8){
				alert("Not a proper SMU ID");
				return;
			}
			console.log(uid);
			console.log(sid);
			$.ajax({
				url: "api/oastuff.php/user",
				type: "post",
				dataType: "JSON",
				data:{
					id: uid,
					SMU: sid
				},
				success: function(result){
					if (result.result == -1){
						alert("Wrong Name/ID pair, please try again");
						return;
					}

					$('body').html("<header>YOUR SECRET SANTA RECIPIENT IS: " + result.name + "</header>");
					setCookie("reciever", result.name, 60);
					console.log(result);
				},
				error: function(){
				}
			});
		});
	}
});