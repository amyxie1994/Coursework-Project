<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>DASHGUM - Bootstrap Admin Template</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet"/>

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->




    <style type="text/css">


        #divbg
        {
            width: 100%;
            height: 100%;
            position: absolute;
            z-index: 999;
            top: 0px;
            left: 0px;
            filter: alpha(opacity=50);
            opacity: 0.5;
            background-color: #AAAAAA;
        }
        #diveditcontent
        {
            width: 630px;
            height: 150px;
            position: absolute;
            z-index: 1000;
            background-color: #444444;
        }
        #divheader
        {
            width: 100%;
            height: 25px;
            background-color: #BB5500;
        }
    </style>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript">
        //window.onload = function () {
        //setinterval(show_status,3000);
        job_id;
        status;
        refreshIntervalId;
        previous_status = [];

        function update(job_ID){
            job_id = job_ID;
            show_status();

            refreshIntervalId = setInterval("show_status()",2000);
            //alert("nichu");
        };
        function show_status(){
            $.ajax({
                type: "POST",
                url: "php/show_processor_status.php",
                data: {job_id: job_id},
                dataType: "json",
                success: function (data) {
                   /*
                    if(status.localeCompare("Complete") == 0){
                        clearInterval(refreshIntervalId);
                        alert(job_id);

                    }*/
                    var status_data=[];
                    var label_name;
                    var judge=1;
                    for(i = 0;i<data.length;i++){
                        label_name = "Processor".concat(i);
                        status_data[i]={y:Number(data[i]),label:label_name};
                        if(Number(data[i])!=7)
                            judge = 0;
                    }
                    if(judge == 0)
                        draw (status_data);
                    else
                    {
                        clearInterval(refreshIntervalId);
                        Lock_CheckForm();
                        view_job(job_id,"Complete");
                    }
                    //alert("jlkj");
                },
                error: function (xhr, error) {
                    //alert("hahha");
                    alert(xhr.responseText);
                }
            });

        };
        function draw(dps) {
            var chart = new CanvasJS.Chart("chartContainer",{
                title: {
                    text: "Processor Status"
                },
                axisY: {
                    labelFormatter: function(e){
                        if (e.value ==0)
                            return "Start";
                        if (e.value ==1)
                            return "Queueing";
                        else if(e.value ==2)
                            return  "Edge_Detecting";
                        else if(e.value ==3)
                            return  "Edge_detection Complete";
                        else if(e.value ==4)
                            return  "Building GMM";
                        else if(e.value ==5)
                            return  "Adaptive CWSI";
                        else if(e.value ==6)
                            return  "Generating Result";
                        else if(e.value ==7)
                            return  "Complete";
                        else
                            return "Complete";
                    }
                },
                legend :{
                    verticalAlign: 'bottom',
                    horizontalAlign: "center"
                },
                data: [
                    {
                        type: "column",
                        bevelEnabled: true,

                        dataPoints: dps
                    }
                ]
            });

            var other_status;
            var judge_idle =1;
            if(dps[0].y==4)
            {
                judge_idle =0;
                other_status = 3;
            }
            else if(dps[0].y==6)
            {
                judge_idle = 0;
                other_status =7;
            }


            for(var i = 0; i<dps.length;i++)
            {

                var yVal ;
                if(judge_idle==0 && dps[i].y ==9) {
                    yVal = other_status;
                    dps[i] = {label: "Processor"+(i) , y: yVal, color:"#6B8E23 "};
                }
                else {
                    yVal= dps[i].y;
                    dps[i] = {label: "Processor"+(i) , y: yVal, color: "#FF6000"};
                }
               /* boilerColor =
                    yVal == 9 ? "#6B8E23 ":
                        yVal >= 0 ? "#FF6000":
                            null;
                dps[i] = {label: "Processor"+(i) , y: yVal, color: boilerColor};
*/
            }

            chart.render();

        };

        function  locking(job_id){

            document.all.ly.style.display="block";
            document.all.ly.style.width=document.body.clientWidth;
            document.all.ly.style.height=document.body.clientHeight;
            document.all.Layer2.style.display='block';
            update(job_id);
            //show_status(job_id);

        }   ;
        function    Lock_CheckForm(){
            document.all.ly.style.display='none';
            document.all.Layer2.style.display='none';
            return   false;
        }
    </script>

    <script type="text/javascript" src="js/canvasjs.min.js"></script>

    
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript">

    table_update();

    //setInterval(try_append, 1000);
    function append_table(new_tbody, job_id, Image_URL, Platform, Submit_Time, Job_Status,Image_Height, Image_Width,Slurm_ID,duration_Time) {
        //var old_tbody = document.getElementById("ttable").getElementsByTagName('tbody')[0];
        //var table = document.getElementById("ttable");
        //var new_tbody = document.createElement('tbody');
        //populate_with_new_rows(new_tbody);

        var row = new_tbody.insertRow(0);

        //var cell0 = row.insertCell(0);
        //cell0.innerHTML = job_id;

        var cell1 = row.insertCell(0);
        cell1.innerHTML = Image_URL;

        var cell2 = row.insertCell(1);
        if (Platform == 'C')
            cell2.innerHTML = "Cloud";
        else
            cell2.innerHTML = "Spartan";

        var cell3 = row.insertCell(2);
        cell3.innerHTML = Submit_Time;

        var cell4 = row.insertCell(3);
        if(Job_Status.localeCompare("Complete") == 0){
            cell4.innerHTML = duration_Time+"(S)";
        }else{
            cell4.innerHTML ="-"
        }

        var cell5 = row.insertCell(4);
        cell5.innerHTML = Job_Status;


        var cell6 = row.insertCell(5);
        var btn = document.createElement('button1');
        btn.className = "btn-link";
        btn.onclick = function () {
            view_job(job_id, Job_Status);
        };
        var temp = document.createElement('i');
        temp.className = "fa fa-eye";


        var cell7 = row.insertCell(6);
        var btn1 = document.createElement('button');
        btn1.className = "btn btn-danger btn-xs";
        btn1.onclick = function () {
            delete_job(job_id,Slurm_ID,Platform)
        };
        //btn.onclick=function() {()};
        var temp1 = document.createElement('i1');
        temp1.className = "fa fa-trash-o";

        var cell8 = row.insertCell(7);
        if(Job_Status.localeCompare("Complete") == 0){

            var btn2 = document.createElement('button');
            btn2.className = "btn btn-danger btn-xs";
            btn2.onclick = function () {
                download_image(job_id);
            };
            var temp2 = document.createElement('i2');
            temp2.className = "fa fa-download";

            btn2.appendChild(temp2);

            cell8.appendChild(btn2);
        }else{
            cell8.innerHTML = "-";
        }


        btn.appendChild(temp);
        btn1.appendChild(temp1);

        cell6.appendChild(btn);
        cell7.appendChild(btn1);

    };

    //OpenWindow = window.opeban("", "newwin", "height=250, width=250,toolbar=no ,scrollbars=" + scroll + ",menubar=no");
    //写成一行


    function download_image(job_id) {

        window.location = "php/JobDownload.php?job_id="+job_id;

        /* $.ajax({
             type: "POST",
             url: "php/JobDownload.php",
             data: "",
             data: {job_id: job_id},
             dataType: "json",
             success: function (data) {
                 alert(data);
             },
             error: function (xhr, error) {
                 alert(xhr.responseText);
             }
         });*/

    }


    function delete_job(job_id,Slurm_ID,Platform) {
        //alert(job_id);
        $.ajax({
            type: "POST",
            url: "php/JobDelete.php",
            data: "",
            data: {job_id: job_id,slurm_id:Slurm_ID,platform:Platform},
            dataType: "json",
            success: function (data) {
                if (data == "success") {
                    alert("Deleting job successfully");
                    table_update();
                }
                else
                    alert("Fail to delete job, try again!")
                //alert("jlkj");
            },
            error: function (xhr, error) {
                alert(xhr.responseText);
            }
        });
    };


    function view_job(job_id, Job_Status) {
        status = Job_Status;
        if(Job_Status.localeCompare("Complete") == 0){

            $.ajax({
                type: "POST",
                url: "php/getUrl.php",
                data: {job_id: job_id},
                dataType: "json",
                success: function (data) {
                    result_url = data.Result_URL;
                    var newwindow = window.open('result.html', 'newwindow');
                    newwindow.url = result_url;
                },
                error: function (xhr, error) {
                    //alert("hahha");
                    alert(xhr.responseText);
                }
            });
        }else{

            locking(job_id);
        }
    }

    function table_update() {
        $.ajax({
            type: "POST",
            url: "php/getJobStatus.php",
            data: "",
            dataType: "json",
            success: function (data) {

                //alert("ni");
                var row;
                var job_id;
                var Platform;
                //var Submit_ID;
                var Submit_Time;
                var duration_Time;
                var Job_Status;
                var Image_URL;
                var Image_Height;
                var Image_Width;
                var Slurm_ID;
                //$("#tbodyid").empty();
                var old_tbody = document.getElementById("ttable").getElementsByTagName('tbody')[0];
                //old_tbody.empty();
                var table = document.getElementById("ttable");
                var new_tbody = document.createElement('tbody');

                for (var i = 0; i < data.length; i++) {
                    row = data[i];
                    //alert(row);
                    job_id = row.Job_ID;
                    Image_URL = row.Image_URL;
                    Platform = row.Platform;
                    Submit_Time = row.Submit_Time;
                    duration_Time = row.Duration_Time;

                    Job_Status = row.Status_Desc;
                    Image_Height = row.Image_Height;
                    Image_Width = row.Image_Width;
                    Slurm_ID = row.Image_Height;
                    //alert(Image_URL);
                    append_table(new_tbody, job_id, Image_URL, Platform, Submit_Time, Job_Status, Image_Height, Image_Width,Slurm_ID,duration_Time);

                }

                table.replaceChild(new_tbody, old_tbody);
                //alert("jlkj");

            },
            error: function (xhr, error) {
                alert(xhr.responseText);
            }
        });
        //alert(i);
    }

    </script>

    <style type="text/css">
        p {
            color: #333;
            font-family: 'Original Surfer', fantasy;
            font-size: 18px;
            line-height: 20px;
            margin: 0 0 20px;
        }

        a {
            color: #d04764;
            font-family: 'Original Surfer', fantasy;
            font-size: 16px;
        }

    </style>
</head>

<body>

<section id="container">
    <!-- **********************************************************************************************************************************************************
    TOP BAR CONTENT & NOTIFICATIONS
    *********************************************************************************************************************************************************** -->
    <!--header start-->
    <header class="header black-bg">
        <div class="sidebar-toggle-box">
            <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
        </div>
        <!--logo start-->
        <a href="index.html" class="logo"><b>Dashboard</b></a>
        <!--logo end-->
       
        <div class="top-menu">
            <ul class="nav pull-right top-menu">
                <li><a class="logout" href="login.html">Logout</a></li>
            </ul>
        </div>
    </header>
    <!--header end-->

    <!-- **********************************************************************************************************************************************************
    MAIN SIDEBAR MENU
    *********************************************************************************************************************************************************** -->
    <!--sidebar start-->
    <aside>
        <div id="sidebar" class="nav-collapse ">
            <!-- sidebar menu start-->
            <ul class="sidebar-menu" id="nav-accordion">

                <p class="centered"><a href="profile.html"><img src="assets/img/ui-sam.jpg" class="img-circle"
                                                                width="60"></a></p>
                <h5 class="centered"><?php echo $_SESSION['login_user'];?></h5>

                <li class="mt">
                    <a href="index.php">
                        <i class="fa fa-dashboard"></i>
                        <span>Overview</span>
                    </a>
                </li>


                <li class="sub-menu">
                    <a href="blank.php">
                        <i class="glyphicon glyphicon-tasks"></i>
                        <span>Upload Task</span>
                    </a>
                </li>

                <li class="sub-menu">
                    <a class="active" href="#">
                        <i class="fa fa-cogs"></i>
                        <span>Tasks</span>
                    </a>
                </li>

            </ul>
            <!-- sidebar menu end-->
        </div>
    </aside>
    <!--sidebar end-->

    <!-- **********************************************************************************************************************************************************
    MAIN CONTENT
    *********************************************************************************************************************************************************** -->
    <!--main content start-->
    <section id="main-content">





        <section class="wrapper">



        <div id="ly" style="position: absolute; top: 0px; filter: alpha(opacity=60); background-color: #777;
         z-index: 2; left: 0px; display: none;">
            </div>
            <!--          浮层框架开始         -->
            <div id="Layer2" align="center" style="position: absolute; z-index: 3; left: 450px; top: 200px;padding-top: 180px);
         background-color: #fff; display: none; border:1px solid #b3ccff" >
                <table width="540" height="350" border="0" cellpadding="0" cellspacing="0" style="border: 0    solid    #e7e3e7;
             border-collapse: collapse ;" >
                    <tr>
                        <td style="background-color: #73A2d6; color: #fff; padding-left: 4px; padding-top: 2px;
                     font-weight: bold; font-size: 12px;" height="10" valign="middle">

                            <div align="right"><a href=JavaScript:; class="STYLE1" onclick="Lock_CheckForm();">CLOSE</a> &nbsp;&nbsp;&nbsp;&nbsp;</div></td>
                    </tr>
                    <tr>
                        <td height="300" align="center">
                            <div id="chartContainer" style="height: 300px; width: 80%;">


                            </div>

                        </td>
                    </tr>
                </table>
            </div>
            <h3><i class="fa fa-angle-right"></i> Task List</h3>


            <div class="row mt">
                <div class="col-md-12">
                    <div class="content-panel">
                        <table class="table table-striped table-advance table-hover" id = "ttable">
                            <h4><i class="fa fa-angle-right"></i> Task information</h4>
                            <hr>
                            <thead>
                            <tr>
                                <th><i class="fa fa-bullhorn"></i> Image Name</th>
                                <th><i class="fa fa-bullhorn"></i> Platform</th>
                                <th><i class="fa fa-bullhorn"></i> Submit Time</th>
                                <th><i class="fa fa-bullhorn"></i> Execution Time</th>
                                <th><i class="fa fa-bullhorn"></i> Status</th>
                                <th><i class=" fa fa-edit"></i> View </th>
                                <th><i class=" fa fa-edit"></i> Delete</th>
                                <th><i class=" fa fa-edit"></i> Download</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /content-panel -->
                </div><!-- /col-md-12 -->
            </div><!-- /row -->



           


        </section>

        <! --/wrapper -->
    </section><!-- /MAIN CONTENT -->

    <!--main content end-->
    <!--footer start-->

    <!--footer end-->

   

</section>

<!-- js placed at the end of the document so the pages load faster -->
<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="assets/js/jquery.scrollTo.min.js"></script>
<script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>


<!--common script for all pages-->
<script src="assets/js/common-scripts.js"></script>

<!--script for this page-->


</body>
</html>
