/**
 * Created by chrissun on 10/5/17.
 */


function login_verify() {

    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var vars = "username="+username
             + "&password="+password;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            //document.getElementById("fail_info").innerHTML = '*Uncorrect username or password!';
            if(this.responseText.localeCompare("success") != 0){
                document.getElementById("fail_info").innerHTML = '*Uncorrect username or password!';
                shake("login-page");
            }else{
                window.location = "./index.php";
            }
        }
    };
    xhttp.open("GET", "PHPFiles/login_verify.php?"+vars, true);
    xhttp.send();
    return false;
}

function shake(id) {
    var style = document.getElementById(id).style,
        p = [4, 8, 4, 0, -4, -8, -4, 0],
        fx = function () {
            style.marginLeft = p.shift() + 'px';
            if (p.length <= 0) {
                style.marginLeft = 0;
                clearInterval(timerId);
            };
        };
    p = p.concat(p.concat(p));
    timerId = setInterval(fx, 13);
}

//window.onload = function () {
//    shake("txtUserName");
//};


function register() {

    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var re_password = document.getElementById("re_password").value;
    var firstname = document.getElementById("firstname").value;
    var lastname = document.getElementById("lastname").value;

    var error = true;
    if(password.localeCompare(re_password) != 0){
        document.getElementById("fail_info").innerHTML = "*The input passwords are different!"
        shake("login-page");
    }else if(password.localeCompare("") == 0){
        document.getElementById("fail_info").innerHTML = "*The password cannot be null!"
        shake("login-page");
    }else {
        var vars = "username=" + username
            + "&password=" + password
            + "&firstname=" + firstname
            + "&lastname=" + lastname;

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (this.responseText.localeCompare("success") != 0) {
                    document.getElementById("fail_info").innerHTML = '*Username has been used, please try another one!';
                    shake("login-page");
                } else {
                    show_success();
                }
            }
        };
        xhttp.open("GET", "PHPFiles/register.php?" + vars, true);
        xhttp.send();
        return false;
    }
}

function show_success(){
    $('#myModal').modal('show');
}

function gotolgin(){
    window.location = "./login.html";
}