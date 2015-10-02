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
            height: 70%;
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
            height: 30%;
            bottom: 0;
            width: 100%;
            border: 1px solid black;
            padding: 0;
            margin: 0;
        }
        .loader{
            display: none;
        }
        table#file-list td{
            padding: 3px 12px 3px 12px;
        }
        table#file-list,
        table#file-list td {
            border: 1px solid black;
        }
        td.percent{
            background-image: url("/img/progress.gif");
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-position-x: -100px;
            width: 79px;
            text-align: right;
        }
    </style>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>

<body>

<div id="container">
    <div id="content">
        <table id="file-list">
            <thead>
                <tr>
                    <td>(i)</td>
                    <td>File</td>
                    <td>Size</td>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <input id="start-task" type="button" value="Start copy" task="1">
    </div>
</div>

<div id="status">

</div>

</body>

<script>

    var fileList = [];   // contains the list of files that user has in our server

    function logMessage(message){
        if ($("#status").html().length > 1000) {
            $("#status").html(message+"<br/>");
        }else{
            $("#status").append(message+"<br/>");
        }

    }

    function updateFileList(){
        // fetch the list from the server, here simulating some random data
        <?php for($i=0;$i<10;$i++) echo "\t".'fileList.push({ name:"'.uniqid('file-').'.ext", size: '.(10*rand(100,200)).'});'."\n"; ?>

        var content = "";
        for(var i=0;i<fileList.length; i++){
            var file = fileList[i];
            content += '<tr id="file-'+i+'">'+
                    '<td><img src="img/loader.gif" class="loader"/></td>'+
                    '<td>'+file.name+'</td>'+
                    '<td class="percent">'+file.size+'</td>'+
                '</tr>';
        }
        $("#file-list tbody").html(content);
    }

    function requestStatusOfFile(id) {
        $.ajax({
            url: '/status-copy.php',
            data: {
                id: id,
                _task: "copy"
            },
            cache: false,
            scope: this,
            type: "POST",
            success: function(data) {
                if (data.result){
                    if (data.status == "done") {
                        $("#file-" + id + " .loader").attr("src", "/img/checkmark.png");
                        $("#file-" + id +" td.percent").css("background-position-x", "-100px");
                        if (id<fileList.length-1) {
                            setTimeout(function() { requestCopyOfFile(id+1); }, 600);
                        }
                    }else{
                        var percent = parseInt(data.percent);
                        $("#file-" + id +" td.percent").css("background-position-x", "-"+(100-percent)+"px");
                        setTimeout(function() { requestStatusOfFile(id); }, 1500);
                    }
                }else{
                    $("#file-" + id + " .loader").attr("src", "/img/alert.png");
                    $("#file-" + id +" td.percent").css("background-position-x", "-100px");
                    logMessages(data.reason);
                }
            }
        })
    }

    function requestCopyOfFile(id){
        logMessage("requesting copy of file "+fileList[id].name);
        $.ajax({
            url: '/copy.php',
            data: {
                id: id,
                file: fileList[id],
                _task: "copy"
            },
            cache: false,
            scope: this,
            type: "POST",
            success: function(data) {
                if (data.result) {
                    $("#file-" + id + " .loader").show();
                    setTimeout(function() { requestStatusOfFile(id); }, 1500);
                }else {
                    logMessage(data.reason);
                }
            },
            error: function(){
                logMessage(data.reason);
            }
        });
    }

    var interval = 0;
    var id = 0;

    $(function(){
        logMessage("Welcome to asynchronous server side task demo");

        updateFileList();

        $("#start-task").click(function(e){
            e.preventDefault();

            console.log("starting copy...");
            requestCopyOfFile(0);

            return false;
        })
    })
</script>

</html>
