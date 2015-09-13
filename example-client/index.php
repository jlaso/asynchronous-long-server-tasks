<html>

<head>

    <style>
        body{
            height: 100%;
            width: 100%;
            background: lightcyan repeat;
            margin: 0;
            padding: 0;
        }
        #container{
            position: relative;
            width: 100%;
            height: 50%;
        }
        #content{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        #status{
            position: fixed;
            overflow: auto;
            height: 50%;
            bottom: 0;
            width: 100%;
            border: 1px solid black;
            padding: 0;
            margin: 0;
        }
    </style>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>

<body>

<div id="container">
    <div id="content">
        <input id="start-task" type="button" value="Start task" task="1">
    </div>
</div>

<div id="status">

</div>

</body>

<script>

    function logMessage(message){
        if ($("#status").html().length > 1000) {
            $("#status").html(message+"<br/>");
        }else{
            $("#status").append(message+"<br/>");
        }

    }

    function pollingStatus()
    {
        $.ajax({
            url: '/status-task.php',
            data: { id: id },
            type: 'POST',
            success: function(data){
                if (data.result){
                    logMessage("task "+data.id+" "+data.status);
                    if (data.status == "done"){
                        clearInterval(interval);
                        alert("The process is done, you can refresh the win")
                    }
                }else{
                    logMessage(data.reason);
                }
            },
            error: function(){
                alert("something wrong was happened");
            }
        });
    }

    var interval = 0;
    var id = 0;

    $(function(){
        logMessage("Welcome to asynchronous server side task demo");

        $("#start-task").click(function(e){
            e.preventDefault();

            id = $("#start-task").attr("task");
            $.ajax({
                url: '/start-task.php',
                data: { id: id },
                type: 'POST',
                success: function(data){
                    //logMessage(data);
                    if (data.result){
                        logMessage("task "+data.id+" added to the system, will be executed soon!");
                        interval = setInterval(pollingStatus, 1000);
                    }else{
                        logMessage(data.reason);
                    }
                },
                error: function(){
                    alert("something wrong was happened");
                }
            });

            return false;
        })
    })
</script>

</html>
