<?php
    //MYSQL Connection Settings (Not my real password)
    $servername = "localhost";
    $username = "root";
    $password = "CanOfBeans27$$";
    $database = "searchengine";

    //Connect to database
    $conn = new mysqli($servername, $username, $password, $database);

    //Check connection to database
    if ($conn->connect_error) 
    {
        die("[ERROR] Connection to database failed"."\n"."[ERROR] Reason: ".$conn->connect_error."\n");
    }

    //Create crawled and crawling arrays
    $crawled = array();
    $crawling = array();

    //SQL statement to get all URLs in the table
    $sql = "SELECT url FROM websites";

    //Query the SQL
    $result = $conn->query($sql);

    //Get all results
    while($row = mysqli_fetch_assoc($result)) 
    {
        //Append all results to crawled array (No need to crawl them again)
        $crawled[] = $row['url'];
        //Echo to the user that the URL has been removed from the queue
        echo "\e[91m[QUEUE] \e[39mRemoved: \e[36m".$row['url']."\e[39m from queue."."\n";
    }

    //Get contents of crawl queue text file
    $file_contents = file('tocrawl.txt');

    //Loop through queue
    foreach($file_contents as $line)
    {
        //If the line isn't blank
        if($line != "\n")
        {
            //Add the URL to the queue
            $crawling[] = $line;
            //Echo to the user that the URL has been added to the queue
            echo "\e[32m[QUEUE] \e[39mAdded: \e[36m".trim($line)."\e[39m to queue."."\n";
        }
    }

    //Get the details of a parsed URL
    function getDetails($url)
    {
        //Get the global connection
        global $conn;

        //Define header information
        $options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: softSearch/0.1\n"));

        //Create header context
        $context = stream_context_create($options);

        //Surpress DOMDocument Errors - DOMDocument can't parse html5 and this results in many errors. This doesn't impact results
        libxml_use_internal_errors(true);
        //Create a new DOMDocument object
        $doc = new DOMDocument();
        //Load the parsed URL with UTF-8 encoding to the DOMDocument object
        $doc->loadHTML(mb_convert_encoding(file_get_contents($url, false, $context), 'HTML-ENTITIES', 'UTF-8'));
        $doc->encoding = 'utf-8';
        //Unsurpress DOMDocument Errors
        libxml_use_internal_errors(false);

        //Get document title
        $title = $doc->getElementsByTagName("title");

        //Check if the title exists
        if (is_object($title->item(0)))
        {
            //Get the title text and remove any unnecessary white space
            $title = $title->item(0)->nodeValue;
            $title = trim($title);

            //Default description and keywords to blank strings
            $description = "";
            $keywords = "";
            //Get all meta tags in the document
            $metas = $doc->getElementsByTagName("meta");

            //Loop through all meta tags
            for($i=0; $i < $metas->length; $i++)
            {
                $meta = $metas->item($i);

                //Check if meta tag contains the description
                if($meta->getAttribute("name") == strtolower("description"))
                {
                    //Assign the content to the description variable
                    $description = $meta->getAttribute("content");
                }
                
                //Check if meta tag contains the keywords
                if($meta->getAttribute("name") == strtolower("keywords"))
                {
                    //Assign the content to the keywords variable
                    $keywords = $meta->getAttribute("content");
                }
            }

            //Remove any unnecessary white space
            $description = trim($description);

            //THIS CODE IS FOR EXTRACTING DATA IN JSON FORM - THIS IS NOT ENABLED BY DEFAULT

            // Returning JSON

            //if ($title != "" && $description != "" && $url != "")
            //{
                //return '{"Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'"},';
            //}

            //THIS CODE IS FOR INSERTING DATA INTO THE DATABASE - THIS IS ENABLED BY DEFAULT

            //Check if the title, description and url all contain data
            if ($title != "" && $description != "" && $url != "")
            {
                //SQL statement only inserts if the data has not already been input - This shouldn't happen regardless but if a rogue link comes in this will deal with it
                $sql = "INSERT INTO websites (title, description, keywords, url)
                        SELECT * FROM (SELECT '$title', '$description', '$keywords', '$url') AS tmp
                        WHERE NOT EXISTS (SELECT url FROM websites WHERE url = '$url') LIMIT 1;";
                
                //Run the query and check if it was successful
                if ($conn->query($sql) === TRUE) 
                {
                    //Echo what data was input into the database to the user
                    echo "\e[32m[INSERT] \e[39mTitle: ".$title.""."\n";
                    echo "\e[32m[INSERT] \e[39mDescription: ".$description.""."\n";
                    echo "\e[32m[INSERT] \e[39mKeywords: ".$keywords.""."\n";
                    echo "\e[32m[INSERT] \e[39mURL: \e[36m".$url.""."\n";
                    //Remove the url from the queue txt file to ensure double ups don't occur
                    $file_contents = file_get_contents('tocrawl.txt');
                    $file_contents = str_replace($url." ","",$file_contents);
                    file_put_contents('tocrawl.txt',$file_contents);
                } 
                
                //If the query fails echo it
                else 
                {
                    echo "\e[91m[ERROR] \e[39m".$sql."\n"."[ERROR] \e[39mReason: ".$conn->error."\n";
                }
            }

            //If the title, description and/or url are blank
            else
            {
                //Remove the link from the queue txt file but don't add it to the database
                $file_contents = file_get_contents('tocrawl.txt');
                $file_contents = str_replace($url." ","",$file_contents);
                file_put_contents('tocrawl.txt',$file_contents);
            }
        }
    }

    //Check if a URL is safe to be crawled by bots
    function robotsTXT($url, $useragent=false)
    {
        // Original PHP code by Chirp Internet: www.chirpinternet.eu
        // Please acknowledge use of this code by including this header.

        // With some modifications by Joel Wright.

        // parse url to retrieve host and path
        $parsed = parse_url($url);
        ob_start();
        var_dump($parsed);
        $dumped = ob_get_contents();
        ob_end_clean();

        $agents = array(preg_quote('*'));
        if($useragent) $agents[] = preg_quote($useragent);
        $agents = implode('|', $agents);

        // location of robots.txt file
        $robotstxt = @file("http://{$parsed['host']}/robots.txt");

        // if there isn't a robots, then we're allowed in
        if(empty($robotstxt)) return true;

        $rules = array();
        $ruleApplies = false;
        foreach($robotstxt as $line) 
        {
            // skip blank lines
            if(!$line = trim($line)) continue;

            // following rules only apply if User-agent matches $useragent or '*'
            if(preg_match('/^\s*User-agent: (.*)/i', $line, $match)) 
            {
                $ruleApplies = preg_match("/($agents)/i", $match[1]);
            }

            if($ruleApplies && preg_match('/^\s*Disallow:(.*)/i', $line, $regs)) 
            {
                // an empty rule implies full access - no further tests required
                if(!$regs[1]) return true;
                // add rules that apply to array for testing
                $rules[] = preg_quote(trim($regs[1]), '/');
            }
        }

        // check if page is disallowed
        if(str_contains($dumped, '["path"]'))
        {
            foreach($rules as $rule) 
            {
                if(preg_match("/^$rule/", $parsed['path'])) return false;
            }
        }

        // page is allowed
        return true;
    }

    //Follow input URL
    function followLinks($url)
    {
        //Get the global arrays
        global $crawled;
        global $crawling;

        //Define header options
        $options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: softSearch/0.1\n"));

        //Create header context
        $context = stream_context_create($options);

        //Remove whitespace from URL
        $url = trim($url);

        //Surpress DOMDocument Errors - DOMDocument can't parse html5 and this results in many errors. This doesn't impact results
        libxml_use_internal_errors(true);
        //Create a new DOMDocument object
        $doc = new DOMDocument();
        //Load the URL to the DOMDocument object with UTF-8 encoding
        $doc->loadHTML(mb_convert_encoding(file_get_contents($url, false, $context), 'HTML-ENTITIES', 'UTF-8'));
        //Unsurpress errors
        libxml_use_internal_errors(false);

        //Get all a tags in the DOMDocument
        $linklist = $doc->getElementsByTagName("a");

        //Loop through every link
        foreach($linklist as $link)
        {
            //Get the URL of the a tag
            $l = $link->getAttribute("href");

            //URL parsing - makes the URL a proper, parsable URL
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

            //If the URL is allowed to be crawled
            if(robotsTXT($l))
            {
                //If the URL hasn't already been crawled
                if(!in_array($l, $crawled))
                {
                    //Add it to the crawled list
                    $crawled[] = $l;
                    //Add it to the queue
                    $crawling[] = $l;
                    //Append the link to the queue txt file
                    $fp = fopen('tocrawl.txt', 'a');
                    fwrite($fp, $l." "."\n");
                    fclose($fp);
                    //Get the URL details
                    getDetails($l);
                }

                else
                {
                    //Tell the user the URL is already in queue
                    echo "\e[91m[ERROR] \e[39mCan't crawl \e[36m".$l."\n"."\e[91m[ERROR] \e[39mReason: Already in queue"."\n"; 
                }
            }

            else
            {
                //Tell the user the URL isn't allowed to be crawled
                echo "\e[91m[ERROR] \e[39mCan't crawl \e[36m".$l."\n"."\e[91m[ERROR] \e[39mReason: Disallowed"."\n";
            }
        }
        
        //Shift the array to the next URL
        array_shift($crawling);
        //For every URL
        foreach ($crawling as $site)
        {
            //Follow the URL
            followLinks($site);
        }
    }

    //Check if the starting URL is allowed to be crawled
    if(robotsTXT($crawling[0]))
    {
        //Crawl the URL
        echo "\e[32m[QUEUE] \e[39mCrawling \e[36m".$crawling[0]."\n";
        followLinks($crawling[0]);
    }

    else
    {
        //Notify the user that the starting URL is disallowed
        echo "\e[91m[ERROR] \e[39mCan't crawl \e[36m".$crawling[0]."\n"."\e[91m[ERROR] \e[39mReason: Disallowed"."\n";
        echo "\e[91m[NOTICE] \e[39mIf this is your first time running the crawler change the URL located in the tocrawl.txt file"."\n";
    }
?>
