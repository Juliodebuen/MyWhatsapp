<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">
<style type="text/css">

.panel{	
	margin-right: 3px;
}

.users{
	position: absolute;
	width: 200px;
	height:500px;
	/*background-color: yellow;*/
	display: inline-block;
}

.listUsers{
	width: 200px;
	height: 30px;
	background-color: red;
	position:relative;
}


.button {
    background-color: #4CAF50;
    border: none;
    color: white;
	margin-right: 30%;   
	margin-left: 30%;
    text-decoration: none;
    display: block;
    font-size: 16px;
    cursor: pointer;
	width:30%;
    height:40px;
	margin-top: 5px;
	 
}
input[type=text]{
		width:100%;
		margin-top:5px;
		
	}


.chat_wrapper {
	width: 70%;
	height:472px;
	margin-right: auto;
	margin-left: auto;
	background: #3B5998;
	border: 1px solid #999999;
	padding: 10px;
	font: 14px 'lucida grande',tahoma,verdana,arial,sans-serif;
}
.chat_wrapper .message_box {
	background: #F7F7F7;
	height:350px;
		overflow: auto;
	padding: 10px 10px 20px 10px;
	border: 1px solid #999999;
}
.chat_wrapper  input{
	//padding: 2px 2px 2px 5px;
}
.system_msg{color: #BDBDBD;font-style: italic;}
.user_name{font-weight:bold;}
.user_message{color: #88B6E0;}

@media only screen and (max-width: 720px) {
    /* For mobile phones: */
    .chat_wrapper {
        width: 95%;
		height: 40%;
	}
    

	.button{ width:100%;
	margin-right:auto;   
	margin-left:auto;
	height:40px;}
	
	
	
	
	
				
}

</style>
</head>
<body>	
<?php 
$colours = array('007AFF','FF7000','FF7000','15E25F','CFC700','CFC700','CF1100','CF00BE','F00');
$user_colour = array_rand($colours);

if(isset($_POST['nUser']))
	$nUsuario = $_POST['nUser'];
else
	echo '<script> window.location = "/Chat/Index.php"; </script>';
?>


<script src="jquery-3.1.1.js"></script>


<script language="javascript" type="text/javascript">  
$(document).ready(function(){
	//Obtiene la direccion ip local
	var flag = false;
	var myIP = 0;
	window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;   //compatibility for firefox and chrome
	var pc = new RTCPeerConnection({iceServers:[]}), noop = function(){};      
	pc.createDataChannel("");    //create a bogus data channel
	pc.createOffer(pc.setLocalDescription.bind(pc), noop);    // create offer and set local description
	pc.onicecandidate = function(ice){  //listen for candidate events
		if(!ice || !ice.candidate || !ice.candidate.candidate)  return;
		myIP = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/.exec(ice.candidate.candidate)[1];
		pc.onicecandidate = noop;	
		document.getElementById(myIP).remove();
	};

	//crea un objeto webSocket
	var wsUri = "ws://192.168.0.6:9000/server.php"; 	
	websocket = new WebSocket(wsUri);

	Array.prototype.unique=function(element){
		return function(){
	  		return this.filter(element)
	  	}
	}(function(element,index,array){
	  	return array.indexOf(element,index+1)<0
	});

	Array.prototype.clean = function( deleteValue ) {
		for ( var i = 0, j = this.length ; i < j; i++ ) {
			if ( this[ i ] == deleteValue ) {
		    	this.splice( i, 1 );
		      	i--;
		    }
		}
		return this;
	};

	websocket.onopen = function(ev) { // Conexion abierta
		$('#message_box').append("<div class=\"system_msg\">Connected!</div>"); //Notifica al usuario
	}

	$('#salaChat').click(function(){
		$('.chat_wrapper').hide();
		$('#mainChat').show();
	});

	$('#send-btn').click(function(){ //Accion de boton Enviar
		var mymessage = $('#message').val(); //Obtiene el valor del input message
		var myname = '<?php echo $nUsuario; ?>';
		var destino = 'todos'; //etiqueta que indica sera enviado a todos los usuarios
		if(mymessage == ""){ //emtpy message?
			alert("Tu mensaje esta vacio");
			return;
		}
		
		var objDiv = document.getElementById("message_box");
		objDiv.scrollTop = objDiv.scrollHeight;
		//prepare json data
		var msg = {
		message: mymessage,
		name: myname,
		destinatary: destino,
		remitent: myIP,
		color : '<?php echo $colours[$user_colour]; ?>'
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));
		document.getElementById('message').value = '';
	});

	function createNewConversation(ip){
		var listUsers = $('<div>').attr('class','listUsers').attr('id',ip).append(ip).click(function(){
			$('.chat_wrapper').hide();
			var ventanaChat = $('<div>').attr('class', 'chat_wrapper').append(
				$('<div>').attr('class', 'message_box').attr('id', 'message_box'+ip)
			).append(
				$('<div>').attr('class','panel').append(
				//$('<input>').attr('type', 'text').attr('name','Destinatario').attr('id','destino'+ip).attr('placeholder','Direccion ip destino')
			).append(
				$('<input>').attr('type', 'text').attr('name','message').attr('id','message'+ip).attr('placeholder','Escribe tu mensaje aquí').attr('maxlength', '80')
			).append(
				$('<input>').attr('type','button').attr('id','send-btn').attr('class', 'button').attr('value','Enviar').click(function(){
					var mymessage = document.getElementById('message'+ip).value; //$('#message'+ip).val(); //get message text
					var myname = '<?php echo $nUsuario; ?>';
					var destino = ip; //get user name
					if(mymessage == ""){ //emtpy message?
						alert("Tu mensaje esta vacio!");
						return;
					}			
					var objDiv = document.getElementById("message_box"+ip);
					objDiv.scrollTop = objDiv.scrollHeight;
					//prepare json data
					var msg = {
						message: mymessage,
						name: myname,
						destinatary: destino,
						remitent: myIP,
						color : '<?php echo $colours[$user_colour]; ?>'
					};
					//convert and send data to server
					websocket.send(JSON.stringify(msg));
					document.getElementById('message'+ip).value = '';
				})
			)
			);
			//var arrayChat = document.getElementsByClassName('chat_wrapper');
			if(document.getElementById('c'+ip) != null){
				document.getElementById('c'+ip).style.display = 'block';
			}else{
				$('body').append(ventanaChat.attr('id','c'+ip));
				//document.getElementById('destino'+ip).value = ip;
			}
		});
		return listUsers; 
	}
	
	//#### Message received from server?
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		var uname = msg.name; //user name
		var ucolor = msg.color; //color
		var destino = msg.destinatary;
		var remitente = msg.remitent;
		var usersOnline = msg.usersConnected;

		if(type == 'usermsg') 
		{
			if(myIP == remitente){
				var mbox = document.getElementById('message_box'+destino);
				$(mbox).append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
				//$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+remitente+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
			}if(destino == myIP){	
				var listUsers = null;
				if(document.getElementById(remitente) == null){
					listUsers = createNewConversation(remitente);					
					$('.users').attr('id','users').append(listUsers);
				}
				var changeName = document.getElementById(remitente);
				$(changeName).empty().append(uname);
				var mbox = document.getElementById('message_box'+remitente);
				$(mbox).append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");	
			}if(destino == 'todos'){
				$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
			}
		}
		if(type == 'system')
		{
			//$('#message_box').append("<div class=\"system_msg\">"+umsg+"</div>");
			if(umsg.search("disconnected") == -1){
				usersOnline = usersOnline.split('|');				
				usersOnline = usersOnline.unique().clean("");
				if(flag == false){
					for(var i=0;i < usersOnline.length;i++){
						//alert(usersOnline[i]);
						var ip = umsg.replace('connected', '').trim();
						if(document.getElementById(usersOnline[i]) == null && usersOnline[i] != ip){						
							var listUsers = createNewConversation(usersOnline[i]);
							$('.users').attr('id','users').append(listUsers);
							flag = true;
						}
					}
				}else{
					var ip = umsg.replace('connected', '').trim();
					if(document.getElementById(ip) == null){
						var ip = umsg.replace('connected', '').trim();
						var listUsers = createNewConversation(ip);
						$('.users').attr('id','users').append(listUsers);
					}					
				}
				
			}else{
				var ip = umsg.replace('disconnected', '').trim();
				//alert(document.getElementById(ip) +"  :"+ ip)
				document.getElementById(ip).remove();
			}			
		}

	};

	websocket.onerror	= function(ev){$('#message_box').append("<div class=\"system_error\">Error Occurred - "+ev.data+"</div>");}; 
	websocket.onclose 	= function(ev){$('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");}; 

});
</script>
<div class="users">
	<div class="listUsers" id="salaChat">
		Sala de Chat
	</div>
</div>
<div class="chat_wrapper" id="mainChat">
	<div class="message_box" id="message_box"></div>
		<div class="panel">
			<!--<input type="text" name="Destinatario" id="destino" placeholder="Direccion ip destino"> -->
			<input type="text" name="message" id="message" placeholder="Escribe tu mensaje aquí" maxlength="80" 
			onkeydown = "if (event.keyCode == 13)document.getElementById('send-btn').click()"  />
		</div>
		<button id="send-btn" class=button>Enviar</button>
</div>
</body>
</html>