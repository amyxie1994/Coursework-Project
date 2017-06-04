<?session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Dashboard</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="css/font-awesome.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="css/style_fonts.css">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet">

    <script src="js/Chart.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript">

        get_user_status();
        setInterval(get_user_status, 1000);

        function get_user_status(){
            get_system_status();
            get_task_status();
        }
        function get_task_status(){
            $.ajax({
                type: "POST",
                url: "PHPFiles/getUserTaskInfo.php",
                data: "",
                dataType: "json",
                success: function (data) {
                    document.getElementById("total_task_num").innerHTML="Total Task:"+data['Total'];
                    document.getElementById("total_task_desc").innerHTML="You have "+ data['Total'] +" tasks in total."

                    document.getElementById("uncomplete_task_num").innerHTML="Incomplete Task:"+data['Uncomplete'];
                    document.getElementById("uncomplete_task_desc").innerHTML="You have "+ data['Uncomplete'] +" tasks still remain running."

                    document.getElementById("complete_task_num").innerHTML="Complete Task:"+data['Complete'];
                    document.getElementById("complete_task_desc").innerHTML="You have "+ data['Complete'] +" complete tasks."
                },
                error: function (xhr, error) {
                    alert(xhr.responseText);
                }
            });
        };
        function get_system_status() {
            $.ajax({
                type: "POST",
                url: "php/getResourceInfo.php",
                data: "",
                dataType: "json",
                success: function (data) {

                    //console.log(data.Allocated_Resources+" "+data.Idle_Resources+" "+data.Other_Resources);

                    data.forEach(function (element) {
                        console.log(element);

                        if(parseInt(element.Platform_ID) == 1){

                            var cloud_alloc= parseInt(element.Allocated_Resources);
                            var cloud_idle = parseInt(element.Idle_Resources);

                            //alert(spartan_alloc);
                            var total = cloud_alloc+cloud_idle;

                            var d = new Date();
                            //document.getElementById("time1").innerHTML = d.toLocaleTimeString();

                            //$("#spartan_idle").val(10);
                            doughnutData = [
                                {
                                    value: (cloud_alloc/total)*100,
                                    color: "#ee232a"
                                },
                                {
                                    value: (cloud_idle/total)*100,
                                    color: "#52a552"
                                }
                            ];

                            var status = document.getElementById("serverstatus02");
                            var context = status.getContext("2d");
                            context.canvas.width = 140;
                            context.canvas.height = 140;

                            //context.clearRect(0, 0, 140, 140);

                            var options = {
                                animation: false,
                            }

                            window.myDoughnut = new Chart(context).Doughnut(doughnutData, options);
                            document.getElementById("cloud_total_cores").innerHTML = "Cores:"+ total;
                            document.getElementById("cloud_avail_cores").innerHTML = "Available:"+ cloud_idle;
                        }
                        if(parseInt(element.Platform_ID) == 2){
                            var spartan_alloc= parseInt(element.Allocated_Resources);
                            var spartan_idle = parseInt(element.Idle_Resources);
                            var spartan_other = parseInt(element.Other_Resources);

                            //alert(spartan_alloc);
                            var total = spartan_alloc+spartan_idle+spartan_other;

                            var d = new Date();
                            //document.getElementById("time1").innerHTML = d.toLocaleTimeString();

                            //$("#spartan_idle").val(10);
                            doughnutData = [
                                {
                                    value: (spartan_alloc/total)*100,
                                    color: "#ee232a"
                                },
                                {
                                    value: (spartan_idle/total)*100,
                                    color: "#52a552"
                                },
                                {
                                    value: (spartan_other/total)*100,
                                    color: "#FF00FF"
                                }
                            ];

                            var status = document.getElementById("serverstatus03");
                            var context = status.getContext("2d");
                            context.canvas.width = 140;
                            context.canvas.height = 140;

                            //context.clearRect(0, 0, 140, 140);

                            var options = {
                                animation: false,
                            }

                            window.myDoughnut = new Chart(context).Doughnut(doughnutData, options);
                            document.getElementById("spartan_total_cores").innerHTML = "Cores:"+ total;
                            document.getElementById("spartan_avail_cores").innerHTML = "Available:"+ spartan_idle;
                        }
                    });


                },
                error: function (xhr, error) {
                    alert(xhr.responseText);
                }
            });
        };

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
                    <a class="active" href="index.html">
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
                    <a href="basic_table.php">
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
            <div class="row">
                <div class="col-lg-9 main-chart";>

                    <div class="row mt">
                        <div class="col-md-3 col-sm-3 mb">

                        </div>
                        <!-- TWITTER PANEL -->
                        <div class="col-md-4 col-sm-4 mb">
                            <div class="white-panel pn donut-chart">
                                <div class="white-header">
                                    <h5 style="color: black">Cloud Status</h5>
                                </div>
                                <canvas id="serverstatus02" height="140" width="140"></canvas>
                                <br>
                                <br>
                                <footer>
                                    <div>
                                        <div class="pull-left">
                                            <h8  style="color: black"><i  id="cloud_total_cores" class="glyphicon glyphicon-tasks" style="color: black"></i></h8>
                                        </div>
                                        <div class="pull-right">
                                            <h8 style="color: black"><i  id="cloud_avail_cores" class="glyphicon glyphicon-tasks" style="color: black"></i></h8>
                                        </div>
                                    </div>
                                </footer>
                            </div>
                            <! -- /darkblue panel -->
                        </div><!-- /col-md-4 -->

                        <div class="col-md-1 col-sm-1 mb">

                        </div>
                        <!-- TWITTER PANEL -->
                        <div class="col-md-4 col-sm-4 mb">
                            <div class="white-panel pn donut-chart">
                                <div class="white-header">
                                    <h5 style="color: black">Spartan Status</h5>
                                </div>
                                <canvas id="serverstatus03" height="140" width="140"></canvas>
                                <br>
                                <br>
                                <footer>
                                    <div class="pull-left">
                                        <h8  style="color: black" ><i id="spartan_total_cores" class="glyphicon glyphicon-tasks" style="color: black"></i></h8>

                                    </div>

                                    <div class="pull-right">
                                        <h8  style="color: black"><i id="spartan_avail_cores" class="glyphicon glyphicon-tasks" style="color: black"></i></h8>
                                    </div>
                                </footer>
                            </div>
                            <! -- /darkblue panel -->
                        </div><!-- /col-md-4 -->
                    </div><!-- /row -->
                </div><!-- /col-lg-9 END SECTION MIDDLE -->
            </div>
            <! --/row -->


            <div class="row mtbox">
                <div class="col-md-2 col-sm-2 mb">

                </div>

                <div class="col-md-2 col-sm-2 box0">
                    <div class="box1">
                        <span class="li_cloud"></span>
                        <h3 id="total_task_num"></h3>
                    </div>
                    <p id="total_task_desc"></p>
                </div>

                <div class="col-md-1 col-sm-1 mb">

                </div>

                <div class="col-md-2 col-sm-2 box0">
                    <div class="box1">
                        <span class="li_stack"></span>
                        <h3 id="uncomplete_task_num"></h3>
                    </div>
                    <p id="uncomplete_task_desc"></p>
                </div>
                <div class="col-md-1 col-sm-1 mb">

                </div>

                <div class="col-md-2 col-sm-2 box0">
                    <div class="box1">
                        <span class="li_news"></span>
                        <h3 id="complete_task_num"></h3>
                    </div>
                    <p id="complete_task_desc"></p>
                </div>


            </div><!-- /row mt -->


        </section>
    </section>

    <!--main content end-->
    <!--footer start-->
    

    
    <!--footer end-->
</section>



<!-- js placed at the end of the document so the pages load faster -->
<script src = "js/jquery.js" ></script>
<script src="js/jquery-1.8.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="js/jquery.dcjqaccordion.2.7.js"></script>
<script src="js/jquery.scrollTo.min.js"></script>
<script src="js/jquery.nicescroll.js" type="text/javascript"></script>
<script src="js/jquery.sparkline.js"></script>

</body>
</html>
