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

    $starturl = "https://stackoverflow.com/";

    $crawled = array();
    $crawling = array();

    $sql = "SELECT url FROM websites";

    $result = $conn->query($sql);

    while($row = mysqli_fetch_assoc($result)) 
    {
        $crawled[] = $row['url'];
        echo "\e[91mRemoved: \e[36m".$row['url']."\e[39m from queue."."\n";
    }

    $file_contents = file('tocrawl.txt');

    foreach($file_contents as $line)
    {
        if($line != "\n")
        {
            $crawling[] = $line;
            echo "\e[32mAdded: \e[36m".trim($line)."\e[39m to queue."."\n";
        }
    }

    libxml_use_internal_errors(true);

    function getDetails($url)
    {
        global $conn;

        $options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: softSearch/0.1\n"));

        $context = stream_context_create($options);

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding(file_get_contents($url, false, $context), 'HTML-ENTITIES', 'UTF-8'));
        $doc->encoding = 'utf-8';
        libxml_use_internal_errors(false);

        $title = $doc->getElementsByTagName("title");
        if (is_object($title->item(0)))
        {
            $title = $title->item(0)->nodeValue;
            $title = trim($title);

            $description = "";
            $keywords = "";
            $metas = $doc->getElementsByTagName("meta");

            for($i=0; $i < $metas->length; $i++)
            {
                $meta = $metas->item($i);

                if($meta->getAttribute("name") == strtolower("description"))
                {
                    $description = $meta->getAttribute("content");
                }

                if($meta->getAttribute("name") == strtolower("keywords"))
                {
                    $keywords = $meta->getAttribute("content");
                }
            }

            $description = trim($description);

            // Returning JSON

            //if ($title != "" && $description != "" && $url != "")
            //{
                //return '{"Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'"},';
            //}

            //Inserting into DB
            if ($title != "" && $description != "" && $url != "")
            {
                $sql = "INSERT INTO websites (title, description, keywords, url)
                        SELECT * FROM (SELECT '$title', '$description', '$keywords', '$url') AS tmp
                        WHERE NOT EXISTS (SELECT url FROM websites WHERE url = '$url') LIMIT 1;";
                
                if ($conn->query($sql) === TRUE) 
                {
                    echo "New record created successfully"."\n";
                    echo "-------------------------------"."\n";
                    echo "Title: ".$title.""."\n";
                    echo "Description: ".$description.""."\n";
                    echo "Keywords: ".$keywords.""."\n";
                    echo "URL: ".$url.""."\n";
                    echo ""."\n";
                    $file_contents = file_get_contents('tocrawl.txt');
                    $file_contents = str_replace($url." ","",$file_contents);
                    file_put_contents('tocrawl.txt',$file_contents);
                } 
                
                else 
                {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }

            else
            {
                $file_contents = file_get_contents('tocrawl.txt');
                $file_contents = str_replace($url." ","",$file_contents);
                file_put_contents('tocrawl.txt',$file_contents);
            }
        }
    }

    function followLinks($url)
    {
        global $crawled;
        global $crawling;

        $options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: softSearch/0.1\n"));

        $context = stream_context_create($options);

        $url = trim($url);

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding(file_get_contents($url, false, $context), 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors(false);

        $linklist = $doc->getElementsByTagName("a");

        foreach($linklist as $link)
        {
            $l = $link->getAttribute("href");

            if(substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//")
            {
                $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
            }

            else if(substr($l, 0, 2) == "//")
            {
                $l = parse_url($url)["scheme"].":".$l;
            }

            else if(substr($l, 0, 2) == "./")
            {
                $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
            }

            else if(substr($l, 0, 1) == "#")
            {
                $l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
            }

            else if(substr($l, 0, 3) == "../")
            {
                $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
            }

            else if(substr($l, 0, 11) == "javascript:")
            {
                continue;
            }

            else if(substr($l, 0, 7) == "mailto:")
            {
                continue;
            }

            else if(substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http")
            {
                $l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
            }

            if(!in_array($l, $crawled))
            {
                $crawled[] = $l;
                $crawling[] = $l;
                $fp = fopen('tocrawl.txt', 'a');//opens file in append mode  
                fwrite($fp, $l." "."\n");
                fclose($fp);
                getDetails($l);
            }
        }
        
        array_shift($crawling);
        foreach ($crawling as $site)
        {
            followLinks($site);
        }
    }

    followLinks($crawling[0]);
?>
