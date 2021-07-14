<!doctype html>
<?php
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

    if(isset($_GET['p']))
    {
        $page = $_GET['p'];

        $limit = 12;

        $page = ($page - 1) * $limit;

        $sql = "SELECT * FROM Posts LIMIT ".$limit." OFFSET ".$page." ORDER BY ID";

        $sql = "SELECT * FROM websites LIMIT ".$limit." OFFSET ".$page."";
        $result = $conn->query($sql);

        if(isset($_POST['submit']))
        {
            $term = $_POST["term"];
            $sql2 = "SELECT * FROM websites WHERE title LIKE '%$term%' OR description LIKE '%$term%' or keywords LIKE '%$term%' LIMIT ".$limit." OFFSET ".$page."";
            $searchresult = $conn->query($sql2);
        }
    }

    else
    {
        $page = '0';

        $limit = 12;

        $sql = "SELECT * FROM Posts LIMIT ".$limit." OFFSET ".$page." ORDER BY ID";

        $sql = "SELECT * FROM websites LIMIT ".$limit." OFFSET ".$page."";
        $result = $conn->query($sql);

        if(isset($_POST['submit']))
        {
            $term = $_POST["term"];
            $sql2 = "SELECT * FROM websites WHERE title LIKE '%$term%' OR description LIKE '%$term%' or keywords LIKE '%$term%' LIMIT ".$limit." OFFSET ".$page."";
            $searchresult = $conn->query($sql2);
        }
    }
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
        
        <link href="main.css" rel="stylesheet" type="text/css">

        <title>Hello, world!</title>
    </head>
    <body>
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title m-b-0">Search Engine</h4>
                <form method="post">
                    <input type="text" name="term" placeholder="Search programs...">
                    <input type="submit" name="submit" value="Search">
                </form>
            </div>
            <ul class="list-style-none">
                <?php
                    if(isset($_POST['submit']))
                    {
                        if($searchresult->num_rows > 0)
                        {
                            // output data of each row
                            while($row2 = $searchresult->fetch_assoc()) 
                            {
                                echo "
                                <li class='d-flex no-block card-body'>
                                    <div> <a href='".$row2['url']."' class='m-b-0 font-medium p-0' data-abc='true'>".$row2['title']."</a> <span class='text-muted'>".$row2['description']."</span> </div>
                                </li>
                                ";
                            }
                        }

                        else 
                        {
                            echo "0 results";
                        }
                    
                        $conn->close();
                    }

                    else
                    {
                        if ($result->num_rows > 0) 
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
                    
                        $conn->close();
                    }
                ?>
            </ul>
            <?php                
                if(isset($_POST['submit']))
                {
                    $term = $_POST["term"];
                    $rowsql = "SELECT count(*) FROM websites WHERE title LIKE '%$term%' OR description LIKE '%$term%' or keywords LIKE '%$term%'";
                    $rowresultsearched = $conn->query($rowsql);
                    $row = $rowresultsearched->fetch_assoc();
                    echo $row['count(*)']; //Will output the count of number of winners
                }

                else
                {
                    $rowsql = "SELECT count(*) FROM websites";
                    $rowresult = $conn->query($rowsql);
                    $row = $rowresult->fetch_assoc();
                    echo $row['count(*)']; //Will output the count of number of winners
                }

                if(isset($_GET['p']))
                {
                    $p = $_GET['p'];
                }

                else
                {
                    $p = '1';
                }

                $totalpages = $rows/$limit;
                //echo $totalpages;
            ?>
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
