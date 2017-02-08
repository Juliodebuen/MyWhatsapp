<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">

.panel{	
	margin-right: 3px;
}

.users{
	width: 10%;
	height:70%;
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
	//create a new WebSocket object.
	var wsUri = "ws://192.168.17.135:9000/server.php"; 	
	websocket = new WebSocket(wsUri); 

	//Obtiene la direccion ip local
	var myIP = 0;
	window.RTCPeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;   //compatibility for firefox and chrome
	var pc = new RTCPeerConnection({iceServers:[]}), noop = function(){};      
	pc.createDataChannel("");    //create a bogus data channel
	pc.createOffer(pc.setLocalDescription.bind(pc), noop);    // create offer and set local description
	pc.onicecandidate = function(ice){  //listen for candidate events
		if(!ice || !ice.candidate || !ice.candidate.candidate)  return;
		myIP = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/.exec(ice.candidate.candidate)[1];
		pc.onicecandidate = noop;
	};
	
	websocket.onopen = function(ev) { // connection is open 
		$('#message_box').append("<div class=\"system_msg\">Connected!</div>"); //notify users
	}

	$('#send-btn').click(function(){ //use clicks message send button	
		var mymessage = $('#message').val(); //get message text
		var myname = '<?php echo $nUsuario; ?>';
		var destino = $('#destino').val(); //get user name
		//alert(destino);
		if(myname == ""){ //empty name?
			alert("Enter your Name please!");
			return;
		}
		if(mymessage == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}
		//document.getElementById("destino").style.visibility = "hidden";
		
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
	});
	
	//#### Message received from server?
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		var uname = msg.name; //user name
		var ucolor = msg.color; //color
		var destino = msg.destinatary;
		var remitente = msg.remitent;

		if(type == 'usermsg') 
		{
			if(destino == myIP || myIP == remitente )
				$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+remitente+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
		}
		if(type == 'system')
		{
			$('#message_box').append("<div class=\"system_msg\">"+umsg+"</div>");
			var ip = umsg.replace('connected', '');
			//$('#users').append("<p>"+ip+"</p>");
		}
		
		$('#message').val(''); //reset text
		
		var objDiv = document.getElementById("message_box");
		objDiv.scrollTop = objDiv.scrollHeight;
	};
	
	websocket.onerror	= function(ev){$('#message_box').append("<div class=\"system_error\">Error Occurred - "+ev.data+"</div>");}; 
	websocket.onclose 	= function(ev){$('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");}; 
});




</script>
<div class="users" id="users">

</div>
<div class="chat_wrapper">
	<div class="message_box" id="message_box"></div>
		<div class="panel">
			<input type="text" name="Destinatario" id="destino" placeholder="Direccion ip destino">
			<input type="text" name="message" id="message" placeholder="Message" maxlength="80" 
			onkeydown = "if (event.keyCode == 13)document.getElementById('send-btn').click()"  />
		</div>
		<button id="send-btn" class=button>Send</button>
	</div>
</body>
</html>