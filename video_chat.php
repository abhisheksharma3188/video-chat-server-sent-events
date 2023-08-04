<?php
$user_id=uniqid();
$room_name=$_GET['room_name'];
$user_timestamp=time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            background: #0098ff;
            display: flex;
            height: 100vh;
            margin: 0;
            align-items: center;
            justify-content: center;
            padding: 0 50px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        video {
            max-width: calc(50% - 100px);
            margin: 0 50px;
            box-sizing: border-box;
            border-radius: 2px;
            padding: 0;
            background: white;
        }
        .copy {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="copy">Send your URL to a friend to start a video call</div>
    <video id="localVideo" autoplay muted></video>
    <video id="remoteVideo" autoplay></video>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>    
    <script>
        //////////////// initialization of variables below ///////////////
        var user_id='<?php echo $user_id; ?>';
        var room_name='<?php echo $room_name; ?>';
        var user_timestamp=<?php echo $user_timestamp; ?>;
        var last_message_s_no=0;
        var message_data;
        var message_data_last_index;
        //////////////// initialization of variables above ///////////////
    </script>    
    <script>
        //////////////// function to send message below //////////////////
        function send_message(message){
            var formData={"message":message,"room_name":room_name};
            $.ajax({
                url: "send_message.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    //console.log(data);
                    //console.log(JSON.parse(data));
                },
                error: function() {
                    alert('Some Error Occured.');
                }
            });
        }
        //////////////// function to send message above //////////////////
    </script> 
    <script>
        //////////////// code for server sent events below ///////////////
        var source = new EventSource(`fetch_message.php?room_name=${room_name}&user_timestamp=${user_timestamp}`);
        source.onmessage = function(event) {
            //console.log(event.data);
            message_data=JSON.parse(event.data);
            if(message_data.length>0){
                for(i=0;i<message_data.length;i++){
                    if(message_data[i].s_no>last_message_s_no){
                        fetch_message(message_data[i].message);
                    }
                }

                message_data_last_index=message_data.length-1;
                last_message_s_no=message_data[message_data_last_index].s_no;
            }
        }
        //////////////// code for server sent events above ///////////////    
    </script> 
    <script>
        //////////////// function to fetch messages below ////////////////
        function fetch_message(message){
            //console.log(message);
            msg=JSON.parse(message);
            console.log(msg);
            if(msg.hasOwnProperty("user_id")){
                if(msg.user_id!=user_id){
                    if(user_timestamp<msg.user_timestamp){
                        send_message(`{"start_webrtc":"true","offerer_id":"${msg.user_id}"}`);
                    }
                }
            }
            if(msg.hasOwnProperty("start_webrtc")){
                if(msg.offerer_id==user_id){
                    isOfferer=true;
                }
                else{
                    isOfferer=false;
                }
                startWebRTC(isOfferer);
            }
            if(msg.hasOwnProperty('sender_id')){
                if(msg.sender_id==user_id){
                    return;
                }
                if (msg.sdp) {
                // This is called after receiving an offer or answer from another peer
                pc.setRemoteDescription(new RTCSessionDescription(msg.sdp), () => {
                    // When receiving an offer lets answer it
                    if (pc.remoteDescription.type === 'offer') {
                    pc.createAnswer().then(localDescCreated).catch(onError);
                    }
                }, onError);
                } else if (msg.candidate) {
                // Add the new ICE candidate to our connections remote description
                pc.addIceCandidate(
                    new RTCIceCandidate(msg.candidate), onSuccess, onError
                );
                }
            }
        }
        //////////////// function to fetch messages above ////////////////
    </script> 
    <script>
        //////////////// configuration below /////////////////////////////
        const configuration = {
            iceServers: [{
                urls: 'stun:stun.l.google.com:19302'
            }]
        };
        //////////////// configuration below /////////////////////////////

        let pc;

        function onSuccess() {};
        function onError(error) {
          console.log(error);
        };

        /////////////// function to start webrtc below ////////////////////
        function startWebRTC(isOfferer) {
            pc = new RTCPeerConnection(configuration);
            
            // 'onicecandidate' notifies us whenever an ICE agent needs to deliver a
            // message to the other peer through the signaling server
            pc.onicecandidate = event => {
                if (event.candidate) {
                send_message(JSON.stringify({'sender_id':user_id,'candidate': event.candidate}));
                }
            };
            
            // If user is offerer let the 'negotiationneeded' event create the offer
            if (isOfferer) {
                pc.onnegotiationneeded = () => {
                pc.createOffer().then(localDescCreated).catch(onError);
                }
            }
            
            // When a remote stream arrives display it in the #remoteVideo element
            pc.onaddstream = event => {
                /*if(isOfferer==false){
                    alert("you got a call");
                }*/
                remoteVideo.srcObject = event.stream;
            };
            
            navigator.mediaDevices.getUserMedia({
                audio: true,
                video: true,
            }).then(stream => {
                // Display your local video in #localVideo element
                localVideo.srcObject = stream;
                // Add your stream to be sent to the conneting peer
                pc.addStream(stream);
            }, onError);
        }
        /////////////// function to start webrtc above ////////////////////

        /////////////// function localDescCreated below ///////////////////
        function localDescCreated(desc) {
        pc.setLocalDescription(
            desc,
            () => send_message(JSON.stringify({'sender_id':user_id,'sdp': pc.localDescription})),
            onError
        );
        }
        /////////////// function localDescCreated below ///////////////////
    </script>    
    <script>
        send_message(`{"user_id":"${user_id}","user_timestamp":${user_timestamp}}`); 
    </script> 
</body>
</html>