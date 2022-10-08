<?php
$prefix = "/var/www/runs/";
$httpauthUser = 'tor';
$httpauthPass = 'tor';

$user = 'rojal';

$validUsers = [
    'rojal' => [
        'realiziraj' => 'Realiziraj orožje',
        'realizirajStrelivo' => 'Realiziraj strelivo',
        'nabavi' => 'Prevzemi orožje',
        'nabaviStrelivo' => 'Prevzemi strelivo',
    ],
    'rti' => [
        'realiziraj' => 'Realiziraj orožje',
        'izdelaj' => 'Izdelaj orožje',
    ]
];
$buttonsStyle = ["primary", "secondary", "success", "info", "warning"];

function createFile($filesuffix) {
    global $prefix;
    global $user;
    if (!ctype_alpha($filesuffix)) {
        exit;
    }
    echo 1;
    touch($prefix . $user . "_" . $filesuffix);
    exit;
}

function checkFile($filesuffix) {
    global $prefix;
    global $user;
    if (!ctype_alpha($filesuffix)) {
        return 0;
    }
    if (file_exists($prefix . $user . "_" . $filesuffix)) {
        return 1;
    }
    elseif (file_exists($prefix . $user . "_" . $filesuffix . "_run")) {
        return 2;
    }
    return 0;
}

// First check if a username was provided.
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    // If no username provided, present the auth challenge.
    header('WWW-Authenticate: Basic realm="HeckaTOR"');
    header('HTTP/1.0 401 Unauthorized');
    exit; // Be safe and ensure no other content is returned.
}

// If we get here, username was provided. Check password.
if ($_SERVER['PHP_AUTH_USER'] != $httpauthUser || $_SERVER['PHP_AUTH_PW'] != $httpauthPass) {
    exit;
}

if ($_SERVER['REQUEST_URI'] == '/' ) {
    $user = 'rojal';
}
else {
    $user = strtolower(substr($_SERVER['REQUEST_URI'], 1));
}
$user = preg_replace("/[^a-z]+/", "", $user);

if (!key_exists($user, $validUsers)) exit;

if ($_POST['type']) {
    createFile($_POST['type']);
}
elseif ($_POST['checkFile']) {
    echo checkFile($_POST['checkFile']);
    exit;
}

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/docs/4.0/assets/img/favicons/favicon.ico">

    <title>HackaTOR</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/cover/">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.1/css/bootstrap.min.css" integrity="sha512-siwe/oXMhSjGCwLn+scraPOWrJxHlUgMBMZXdPe2Tnk3I0x3ESCoLz7WZ5NTH6SZrywMY+PB1cjyqJ5jAluCOg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  </head>

  <body class="text-center">

    <div class="cover-container d-flex h-100 p-3 mx-auto flex-column">
      <main role="main" class="inner cover">
        <h1 class="cover-heading">HackaTOR / <?php echo $user;?></h1>
        <p class="lead action">
            <?php 
                $i = 0;
                foreach($validUsers[$user] as $buttontype => $buttontext) {
                    ?>
                    <button data-type="<?php echo $buttontype;?>" type="button" class="btn btn-lg btn-<?php echo $buttonsStyle[$i++];?>" <?php if (checkFile($buttontype)>0) echo "disabled"?>><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><?php echo $buttontext;?></button>
                    <?php
                }
            ?>
        </p>
      </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            $(".action button").on("click", function(){
                $.post( "", {
                    type: $(this).attr("data-type"),
                }, function( data ) {

                });
                $(this).prop("disabled", true);
            });
            setInterval(function() {
                $(".action button:disabled").each(function(event){
                    let $button = $(this);
                    $.post( "", {
                        checkFile: $(this).attr("data-type"),
                    }, function( data ) {
                        switch (data) {
                            case "1":
                                break;
                            case "2":
                                $("span", $button).removeClass("d-none");
                                break;
                            default:
                            $("span", $button).addClass("d-none");
                                $button.prop("disabled", false);
                        }
                    });
                });
            }, 1000);
        });


    </script>
    <style>
        /*
        * Globals
        */

        /* Links */
        a,
        a:focus,
        a:hover {
        color: #fff;
        }

        /* Custom default button */
        .btn-secondary,
        .btn-secondary:hover,
        .btn-secondary:focus {
        color: #333;
        text-shadow: none; /* Prevent inheritance from `body` */
        background-color: #fff;
        border: .05rem solid #fff;
        }


        /*
        * Base structure
        */

        html,
        body {
            height: 100%;
            background-color: #333;
        }

        body {
            display: -ms-flexbox;
            display: -webkit-box;
            display: flex;
            -ms-flex-pack: center;
            -webkit-box-pack: center;
            justify-content: center;
            color: #fff;
            text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);
            box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);
        }

        .cover-container {
            max-width: 75em;
        }

        /*
        * Cover
        */
        .cover {
        padding: 0 1.5rem;
        }
        .cover .btn-lg {
            margin-top: 0.3em;
            padding: .75rem 1.25rem;
            font-weight: 700;
        }

    </style>
  </body>
</html>