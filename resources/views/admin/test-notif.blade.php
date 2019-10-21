<html>
<title>Firebase Messaging Demo</title>
<style>
    div {
        margin-bottom: 15px;
    }
</style>
<body>
Token <div id="token"></div>
Msg <div id="msg"></div>
Notis <div id="notis"></div>
Error <div id="err"></div>

<script src="https://www.gstatic.com/firebasejs/7.2.1/firebase-app.js"></script>

<!-- Add additional services that you want to use -->
<script src="https://www.gstatic.com/firebasejs/7.2.1/firebase-messaging.js"></script>
{{--<script src="{{ asset('js/fcm-notif.js') }}"></script>--}}
<script>
    MsgElem = document.getElementById("msg")
    TokenElem = document.getElementById("token")
    NotisElem = document.getElementById("notis")
    ErrElem = document.getElementById("err")
    // Initialize Firebase
    // TODO: Replace with your project's customized code snippet

    var config = {
        apiKey: "{{env('FCM_API_KEY')}}",
        authDomain: "{{env('FCM_DOMAIN')}}.firebaseapp.com",
        databaseURL: "https://{{env('FCM_DOMAIN')}}.firebaseio.com",
        projectId: "{{env('FCM_DOMAIN')}}",
        storageBucket: "{{env('FCM_DOMAIN')}}.appspot.com",
        messagingSenderId: "{{env('FCM_MESSANGING_SENDER')}}"
    };
    firebase.initializeApp(config);

    const messaging = firebase.messaging();
    messaging
        .requestPermission()
        .then(function () {
            MsgElem.innerHTML = "Notification permission granted."
            console.log("Notification permission granted.");

            // get the token in the form of promise
            return messaging.getToken()
        })
        .then(function(token) {
            TokenElem.innerHTML = "token is : " + token
        })
        .catch(function (err) {
            ErrElem.innerHTML =  ErrElem.innerHTML + "; " + err
            console.log("Unable to get permission to notify.", err);
        });

    messaging.onMessage(function(payload) {
        console.log("Message received. ", payload);
        // NotisElem.innerHTML = NotisElem.innerHTML + JSON.stringify(payload)
        NotisElem.innerHTML = NotisElem.innerHTML + payload.data.message
    });
</script>

</body>

</html>
