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
            height: 40%;
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
        div.center {
            width: 100%;
            text-align: center;
        }
    </style>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
</head>

<body>

<div class="center">
    <h2>Copying multiple files demo</h2>
    <h3><a href="http://www.phpclasses.org/blog/package/9383/">Take a look on the blog of this class</a></h3>
</div>

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
        <?php for($i=0;$i<3;$i++) echo "\t".'fileList.push({ name:"'.uniqid('file-').'.ext", pending: true, size: '.(100*rand(200,300)).'});'."\n"; ?>

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

    function requestStatusOfFiles() {
        var ids = "";
        for(var i=0;i<fileList.length; i++){
            if (fileList[i].pending) {
                ids += i + ",";
            }
        }
        $.ajax({
            url: '/status-copy-multi.php',
            data: {
                ids: ids,
                _task: "copy-multi"
            },
            cache: false,
            scope: this,
            type: "POST",
            success: function(data) {
                if (data.result){
                    var pending = 0;
                    for (var i=0; i<data.info.length; i++){
                        var info = data.info[i];
                        info.id = parseInt(info.id);
                        switch (info.status) {
                            case "done":
                                updateRowId(info.id, "checkmark", null);
                                fileList[info.id].pending = false;
                                break;
                            case "error":
                                updateRowId(info.id, "alert", null);
                                break;
                            default:
                                pending++;
                                updateRowId(info.id, null, parseInt(info.percent));
                                break;
                        }
                    }
                    console.log("pending = "+pending);
                    if (pending > 0) {
                        setTimeout(function () { requestStatusOfFiles(); }, 800);
                    }
                }else{
                    logMessage(data.reason);
                }
            }
        })
    }

    function updateRowId(id, img, percent) {
        if (img != null) $("#file-" + id + " .loader").attr("src", "/img/"+img+".png");
        if (percent == null) percent = 100; else percent = 100-percent;
        $("#file-" + id +" td.percent").css("background-position-x", "-"+percent+"px");
    }

    function requestCopyOfFile(id){
        logMessage("requesting copy of file "+fileList[id].name);
        $.ajax({
            url: '/copy-multi.php',
            data: {
                id: id,
                file: fileList[id],
                _task: "copy-multi"
            },
            cache: false,
            scope: this,
            type: "POST",
            success: function(data) {
                if (data.result) {
                    $("#file-" + id + " .loader").show();
                    if (id == 0) {
                        setTimeout(function () { requestStatusOfFiles(); }, 500);
                    }
                }else {
                    logMessage(data.reason);
                }
            },
            error: function(){
                logMessage("some error happened trying to start task");
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

            $(this).hide();

            console.log("starting copy...");

            for(var i=0; i<fileList.length; i++) {
                requestCopyOfFile(i);
            }

            return false;
        })
    })
</script>

</html>
