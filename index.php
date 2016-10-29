<html>
<head>
<title>The Little Shortener Director</title>
<style type="text/css">
    html,body {
  height:100%;
  width:100%;
  margin:0;
  color: white;
}
body , body {
    font-family:Arial;
  background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAWCAMAAADto6y6AAAAD1BMVEUgICAAAQAoKCg1NTU/Pz+suxeKAAAAOklEQVR42u2QwQkAMAgDo3b/mftSlKO4QO+VINGgZPJkarMyXb8TcaJM1/IJBtiYFh3WBDpsx8l/CbgSVgPZd9YSYQAAAABJRU5ErkJggg==')
}
form {
    position: absolute;
top: 50vh;
    width: 100%;
    transform: translateY(-50%);
    text-align: center;
  margin:auto;
}

input { font-size:16px; border-color:#cccccc; padding:7px; text-align:center; border-width:2px; border-radius:6px; border-style:solid; text-shadow:0px 0px 4px rgba(62,62,62,.38);  } 
input:focus { outline:none; } 

.title 
{
color: rgba(0, 0, 0, 0.6);
font-size: 50px;
background-color: rgba(110, 110, 110, 0.5);
text-shadow: rgba(255, 255, 255, 0.2) 3px 2px 3px;
}

.myButton {
    -moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
    -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
    box-shadow:inset 0px 1px 0px 0px #ffffff;
    background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf));
    background:-moz-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
    background:-webkit-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
    background:-o-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
    background:-ms-linear-gradient(top, #ededed 5%, #dfdfdf 100%);
    background:linear-gradient(to bottom, #ededed 5%, #dfdfdf 100%);
    filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf',GradientType=0);
    background-color:#ededed;
    -moz-border-radius:6px;
    -webkit-border-radius:6px;
    border-radius:6px;
    border:1px solid #dcdcdc;
    display:inline-block;
    cursor:pointer;
    color:#777777;
    font-family:Arial;
    font-size:15px;
    font-weight:bold;
    padding:6px 24px;
    text-decoration:none;
    text-shadow:0px 1px 0px #ffffff;
}
.myButton:hover {
    background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #dfdfdf), color-stop(1, #ededed));
    background:-moz-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
    background:-webkit-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
    background:-o-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
    background:-ms-linear-gradient(top, #dfdfdf 5%, #ededed 100%);
    background:linear-gradient(to bottom, #dfdfdf 5%, #ededed 100%);
    filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dfdfdf', endColorstr='#ededed',GradientType=0);
    background-color:#dfdfdf;
}
.myButton:active {
    position:relative;
    top:1px;
}

}
</style>
</head>
<body onload="document.getElementById('url').focus();document.getElementById('url').select()">

<center><div class="title">Little Shortener Director</div></center>

<?php

include('conf.php');

function base62_encode($num){
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
 
    while($num >= 62) {
        $r = $num%62;
        $result = $chars[$r].$result;
        $num = $num/62;
    }
    return $chars[$num].$result;
}


function base62_decode($id){
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $final = 0;
    for ($i=0; $i<strlen($id); $i++) {
        $value = strpos($chars, $id[$i]);
        $num = $value * pow(62, strlen($id)-($i+1));
        $final += $num;
    }
    return $final;
}

function erreur($id)
{
    $erreur = array(0 => 'This is not a link',
        1 => 'The ID is invalid',
        2 => 'Ce n\'est pas la peine de jouer avec l\'anti aspi ! :p',
        3 => 'This link is forbidden');
    $txt = '<form>'.$erreur[$id].'</form>';
    return $txt;

}

if(empty($_GET))
{
    if (empty($_POST))
    {
        // show a form
        echo '<form name="form" method="POST">
        <input type="text" id="url" name="url" placeholder="Enter you link" onfocus="this.placeholder = \'\'"/><br /><br />
        <input type="checkbox" name="burn" id="burn"><label for="burn">One time link</label><br /><br />
        <input class="myButton" type="submit" value="Raccourcir"/>
        </form>';
    }
    else
    {
        // we insert the url
        $c = mysqli_connect($host, $user, $pass, $db) or die('Erreur');
        $url = mysqli_escape_string($c, htmlentities($_POST['url']));
        $burn = false;
        if (!empty($_POST['burn'])) { $burn = true ;}
        if (strpos($url, 'http') === 0)
        {
            $date = time();
            $id = 0;
            if (!$burn)
            {
                $req = mysqli_query($c, "SELECT id FROM links WHERE `long`='".$url."';");
                $id = mysqli_fetch_array($req)[0];
            }
            if (empty($id))
            {
                mysqli_query($c, "INSERT INTO links VALUES ('', '".$url."', '".$date."', '".$burn."')") or die(mysqli_error($c));
                $req = mysqli_query($c, "SELECT id FROM links ORDER BY id DESC LIMIT 1");
                $id = mysqli_fetch_array($req)[0];
            }
            echo '<form><input type="text" id="url" value="'.$domain.parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH).'?'.base62_encode($id).'"/><br /><br /><input type="submit" class="myButton" value="Shorten another link" /></form><br /><br />';
        }
        else
        {
            echo erreur(0);
        }
    }
}
else
{
    $c = mysqli_connect($host, $user, $pass, $db) or die('Erreur');
    $id = mysqli_escape_string($c, htmlentities(base62_decode(strval(key($_GET)))));
    if (is_int((intval($id))))
    {
        $req = mysqli_query($c, "SELECT `long`, burn FROM `links` WHERE id=".$id.";");
        list($link, $burn) = mysqli_fetch_array($req);
        if (empty($link))
        {
            echo erreur(1);
        }
        else
        {
            if ($burn)
            {
                mysqli_query($c, "DELETE FROM`links` WHERE id=".$id.";");
            }
            if (strpos($link, 'newbiecontest.org/content/anti-aspi/') == false)
            {
                @header('Location: '.$link);
            }
            else
            {
                echo erreur(2);
            }
        }

    }
    else
    {
        echo erreur(1);
    }

}


?>

</body>
</html>
