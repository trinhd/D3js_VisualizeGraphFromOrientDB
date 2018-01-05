<!DOCTYPE html>
<html>

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <title>Chương trình trực quan hóa chủ đề</title>
</head>

<body onload="getSubject()">
    <div class="container">
        <div class="jumbotron">
            <h2>CHƯƠNG TRÌNH TRỰC QUAN HÓA CHỦ ĐỀ</h2>
			<h3>Luận văn thạc sĩ - Nguyễn Hồ Duy Tri</h3>
			<h5>Trường Đại học Công Nghệ Thông Tin - ĐHQG TP.HCM</h5>
			<img src="css/logo_small.png" alt="Logo UIT" height="100px" />
        </div>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Chọn chủ đề
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
            </ul>
        </div> 
        <!-- <button type="button" onclick="createGraphOfSubject('')">Change Content</button> -->
    </div>
    <div align='center' id="d3_selectable_force_directed_graph" style="width: 930px; height: 600px; margin-top: -38px; margin-left: 417px; margin-bottom: 12px">
        <svg />
    </div>

<link rel='stylesheet' href='css/d3-selectable-zoomable-force-directed-graph.css'>
<link rel='stylesheet' href='css/bootstrap.css'>
<!-- <script src="js/jquery.min.js"></script> -->
<script src="js/d3.min.js"></script>
<script src="js/d3-brush-lite.js"></script>
<script src="js/d3-selectable-force-directed-graph.js"></script>
<!-- <script src="js/bootstrap.min.js"></script> -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
</body>
<script type="text/javascript">
    function createGraphOfSubject(subject) {
        var svg = d3.select('#d3_selectable_force_directed_graph');
        var xhttp;
        if (window.XMLHttpRequest) {
            // code for modern browsers
            xhttp = new XMLHttpRequest();
        } else {
            // code for old IE browsers
            xhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var graph = JSON.parse(JSON.stringify(eval("(" + this.responseText + ")")));
                createSelectableForceDirectedGraph(svg, graph);
            }
        };
        xhttp.open("GET", "OrientDBUtils.php?q=" + subject, true);
        xhttp.send();
    }

    function getSubject() {
        var xhttp;
        if (window.XMLHttpRequest) {
            // code for modern browsers
            xhttp = new XMLHttpRequest();
        } else {
            // code for old IE browsers
            xhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var subject = JSON.parse(JSON.stringify(eval("(" + this.responseText + ")")));
                var ddmenu = document.getElementsByClassName("dropdown-menu")[0];
                var temp = "";
                subject.forEach(function(item){
                    //window.alert(typeof(item));
                    //temp = temp + '<li value="' + item + '">' + item + '</li>';
                    temp = temp + '<li onclick="createGraphOfSubject(\''+ item +'\');" role="presentation"><a role="menuitem" href="#">' + item + '</a></li>';
                });
                //window.alert(temp);
                ddmenu.innerHTML = temp;
            }
        };
        xhttp.open("GET", "OrientDBUtils.php?q=getSubject", true);
        xhttp.send();
    }
</script>

<!-- <script type="text/javascript">
var user = "admin";
var pass = "admin";

function base64(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

function loadDoc() {
    var xhttp;
    if (window.XMLHttpRequest) {
        // code for modern browsers
        xhttp = new XMLHttpRequest();
    } else {
        // code for old IE browsers
        xhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("demo").innerHTML = this.responseText;
        }
    };
    //xhttp.open("POST", "http://localhost:2480/command/CoOccurrenceGraph/sql/select from dinh", true);
    xhttp.open("GET", "http://localhost:2480/server", true);
    //xhttp.setRequestHeader("Accept", "application/json");
    xhttp.setRequestHeader("Accept", "application/json,gzip,deflate");
    xhttp.setRequestHeader("Content-Length", 0);
    xhttp.setRequestHeader("Access-Control-Allow-Origin", "*");
    xhttp.setRequestHeader("Authorization", "Basic " + base64(user + ":" + pass));
    xhttp.send();
}
</script> -->

</html>