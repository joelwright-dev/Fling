<!doctype html>
<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $servername = "localhost";
    $username = "root";
    $password = "CanOfBeans27$$";
    $database = "searchengine";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) 
    {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_GET['p'])) 
    {
        $p = $_GET['p'];
    } 

    else 
    {
        $p = 1;
    }

    $no_of_records_per_page = 10;
    $offset = ($p-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(*) FROM websites";
    $result = mysqli_query($conn,$total_pages_sql);
    $total_rows = mysqli_fetch_array($result)[0];
    $total_pages = ceil($total_rows / $no_of_records_per_page);

    if(isset($_GET['q']))
    {
        $q = $_GET['q'];
        $sql = "SELECT * FROM websites WHERE title LIKE '%$q%' OR description LIKE '%$q%' or keywords LIKE '%$q%' LIMIT $offset, $no_of_records_per_page"; 
    }

    else
    {
        $sql = "SELECT * FROM websites LIMIT $offset, $no_of_records_per_page"; 
    }

    $result = $conn->query($sql);
?>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
        
        <link href="main.css?v=1.1" rel="stylesheet" type="text/css">

        <title>Hello, world!</title>
    </head>
    <body>
        <div class="d-flex align-items-center">
            <div class="card d-flex align-items-center">
                <div class="card-body text-left d-flex align-items-center">
                    <img class="flinglogo-s" src="images/Logo.png"/>
                    <form class="searchform" method="GET">
                        <input class="fullwidthtext" type="text" name="q" placeholder="Search the web...">
                        <input type="submit" value="Search">
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <ul class="list-style-none">
                <?php
                        if($result->num_rows > 0)
                        {
                            // output data of each row
                            while($row = $result->fetch_assoc()) 
                            {
                                echo "
                                <li class='d-flex no-block card-body'>
                                    <div> <a href='".$row['url']."' class='m-b-0 font-medium p-0' data-abc='true'>".$row['title']."</a> <span class='text-muted'>".$row['description']."</span> </div>
                                </li>
                                ";
                            }
                        }

                        else 
                        {
                            echo "0 results";
                        }
                ?>
            </ul>
            <div class="container" style="padding-top: 40px;">
                <div class="row">
                    <div class="col-xs-12 center-block" style="background-color:grey;">
                        <ul class="pagination justify-content-between w-50 position-absolute bottom-0 start-50 translate-middle-x">
                            <li><a href="?p=1<?php if(isset($_GET['q'])){echo"&q=".$_GET['q']."";}?>">First</a></li>
                            <li class="<?php if($p <= 1){ echo 'disabled'; } ?>">
                                <a href="<?php if($p <= 1){ echo '#'; } else { echo "?p=".($p - 1); } if(isset($_GET['q'])){echo"&q=".$_GET['q']."";}?>">Prev</a>
                            </li>
                            <li class="<?php if($p >= $total_pages){ echo 'disabled'; } ?>">
                                <a href="<?php if($p >= $total_pages){ echo '#'; } else { echo "?p=".($p + 1); } if(isset($_GET['q'])){echo"&q=".$_GET['q']."";}?>">Next</a>
                            </li>
                            <li><a href="?p=<?php echo $total_pages; if(isset($_GET['q'])){echo"&q=".$_GET['q']."";}?>">Last</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
