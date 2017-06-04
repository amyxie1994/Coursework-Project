
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
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet"/>

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <script src="js/dropzone.js"></script>
    <script src="js/upload.js"></script>

    <script type="text/javascript">

        function Info(platform) {
            //alert(platform);
            if (platform == 'Spartan') {
                var div = document.getElementById("Cloud_Variables");
                div.style.display = 'none';
                div = document.getElementById("Spartan_Variables");
                div.style.display = 'block';

            }
            else if (platform == 'Cloud') {
                var div = document.getElementById("Spartan_Variables");
                div.style.display = 'none';
                div = document.getElementById("Cloud_Variables");
                div.style.display = 'block';
            }
            else {
                var div = document.getElementById("Spartan_Variables");
                div.style.display = 'block';
                div = document.getElementById("Cloud_Variables");
                div.style.display = 'block';
            }

        }
        //function CloudInfo() {
        //var x = document.getElementById("Cloud_Variables")
        //  x.style.display='block';
        //}
    </script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->


    <style>
        .fileUpload {
            position: relative;
            overflow: hidden;
            margin: 5px;
        }

        .fileUpload input.upload {
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            padding: 0;
            font-size: 10px;
            cursor: pointer;
            opacity: 0;
            filter: alpha(opacity=0);
        }

        h1 {
            color: #d04764;
            font-family: 'Lobster', cursive;
            font-size: 36px;
            font-weight: normal;
            line-height: 48px;
            margin: 0 0 18px;
            text-shadow: 1px 0 0 #fff;
        }

        h2 {
            color: #2CA4B0;
            font-family: 'Oleo Script', cursive;
            font-size: 24px;
            font-weight: normal;
            line-height: 32px;
            margin: 0 0 18px;
            text-shadow: 1px 0 0 #fff;
        }

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
                    <a class="active" href="blank.php">
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
        <section class="wrapper site-min-height">
            <h3><i class="fa fa-angle-right"></i> Upload Task</h3>


            <!-- **********************************************************************************************************************************************************
            MAIN CONTENT
            *********************************************************************************************************************************************************** -->
            <div class="panel panel-default" style="height: 700px;">
                <p class="panel-heading no-collapse">Image Processing Job Submit</p>
                <div class="panel-body">
                    <form action="php/process.php" method="post" enctype="multipart/form-data">


                        <p>Choose the platform to process:</p>
                        <input type="radio" name="platform" value="Spartan" onclick="Info('Spartan')">
                        <a>HPC(Spartan)</a><br>
                        <input type="radio" name="platform" value="Cloud" onclick="Info('Cloud')"><a> Cloud</a><br>
                        <input type="radio" name="platform" value="Both" onclick="Info('Both')"> <a>Both</a><br>
                        <br>
                        <p> Upload image file for processing:</p>


                        <input type="file" class="upload" name="fileToUpload" id="fileToUpload" accept="image/*"/>


                        <br><br>
                        <p> Fill in minimum temperature and maximum temperature</p>

                        <label><a>Min_temp</a></label>
                        <input type="text" id="min_temp" name="min_temp">
                        <a>Max_temp</a>
                        <input type="text" id="max_temp" name="max_temp">
                        <br><br>
                        <p> Upload specie info file :</p>

                        <input type="file" class="upload" name="speciefileToUpload" id="speciefileToUpload"
                               accept=".csv, text/csv"/>

                        <br><br>
                        <div id="Cloud_Variables" style="display: none">
                            <p> For cloud users choose core numbers:</p>

                            <a> Core num: </a>
                            <select name=cloud_coreNum>
                                <option value=1 selected="selected">1</option>
                                <option value=2>2</option>
                                <option value=3>3</option>
                                <option value=4>4</option>
                            </select>

                            <br><br>
                            <p> For cloud users choose node numbers:</p>
                            <a> Node num: </a>
                            <select name=cloud_nodeNum>
                                <option value=1 selected="selected">1</option>
                                <option value=2>2</option>
                                <option value=3>3</option>
                            </select>
                        </div>
                        <div id="Spartan_Variables" style="display: none">

                            <p> For spartan users, type in your username and password.</p>

                            <label><a>Username</a></label>
                            <input type="text" id="spartanUsername" name="spartanUsername">

                            <label><a>Password</a></label>
                            <input type="password" id="spartanPwd" name="spartanPwd"><br><br>

                            <p>For HPC(Spartan) users choose cores number:</p>
                            <a>cores Num : </a>
                            <select name=coreNum>
                                <option value=1 selected="selected">1</option>
                                <option value=2>2</option>
                                <option value=3>3</option>
                                <option value=4>4</option>
				<option value=5>5</option>
				<option value=6>6</option>
				<option value=7>7</option>
				<option value=8>8</option>
                            </select>
                            <br><br>
                        </div>

                        <input class="fileUpload btn btn-primary" type="submit" value="Run" name="submit">

                    </form>
                </div>
            </div>


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
<script src="assets/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="assets/js/jquery.scrollTo.min.js"></script>
<script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>


<!--common script for all pages-->
<script src="assets/js/common-scripts.js"></script>

<!--script for this page-->

<script>
    //custom select box

    $(function () {
        $('select.styled').customSelect();
    });

</script>

</body>
</html>
