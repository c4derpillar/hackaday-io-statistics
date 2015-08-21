///////////////////////////////////
//Hackaday.io Statistics Scraper v0.1
//By Taylor Wass
//taylor.wass@uqconnect.edu.au
//
//Features:
//Collects fields under 'Projects' for every project on Hackaday.io. 
//Usually I configure the server to add a timestamp and have scheduled the script
//every 12 hours to avoid going over the request limit.
//
//
//How to use:
//0. Create an SQL database containing the following fields: 
//project_id, url, owner_id, name, summary, image_url, views, comments, followers,
//skulls, logs, details, instructions, components, images, created, updated
//1. Enter your SQL details below
//2. Enter your dev.hackaday.io API key below (register a project)
//
//You might want to consider increasing max_execution_time in PHP.ini
//Needs PHP 5
//More to come!

        <?php
            echo "test";
            // Please change these details to that of your MySQL server
            $servername = "localhost";
            $username = "root";
            $password = "root";
            $dbname = "hackadayqf";
            $apikey = '';
        
            // Set up arrays to store data
            $qflist = array();
            $jsonarray = array();
            
            // Do an initial scrape to find out how many pages of projects there are
            
            $getinfo = file_get_contents('https://api.hackaday.io/v1/projects?api_key=' . $apikey);
            // Parse the json into PHP as an object
            $json = json_decode($getinfo);
            // This loop writes each page containing 50 projects to an array
            var_dump($json->last_page);
            
            for ($i = 1; $i <= $json->last_page; $i++) {
                echo 'Writing page ' . $i . '<br>';
                //echo 'https://api.hackaday.io/v1/projects?api_key=Ork7mVmE7DKYdquZ&page=' . $i;
                $jsonarray[] = json_decode(file_get_contents('https://api.hackaday.io/v1/projects?api_key=' . $apikey . '&page='. $i));
                echo 'Done<br>';
            }
            // Loop through the array containing every project
            foreach ($jsonarray as $ind=>$page) {
                // Loop through each project and export the parameters we want
                foreach ($page->projects as $pjktnum=>$pjkt) {
                    // Note: this should probably be a persistent connection
                    // but I could not get it to work.
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    } 
                    // SQL query to update database. Some chars must be escaped
                    $sql = "INSERT INTO " . $dbname . " (project_id, url, owner_id, name, summary, image_url, views, comments, followers, skulls, logs, details, instructions, components, images, created, updated)
                    VALUES ('". $pjkt->id . "','" . mysql_real_escape_string($pjkt->url) . "','" . $pjkt->owner_id . "','" . mysql_real_escape_string($pjkt->name) . "','" . mysql_real_escape_string($pjkt->summary) . "','" . mysql_real_escape_string($pjkt->image_url) . "','" . $pjkt->views . "','" . $pjkt->comments . "','" . $pjkt->followers . "','" . $pjkt->skulls . "','" . $pjkt->logs . "','" . $pjkt->details . "','" . $pjkt->instruction . "','" . $pjkt->components . "','" . $pjkt->images . "','" . $pjkt->created . "','" . $pjkt->updated . "'" .  ");";
                    if (mysqli_multi_query($conn, $sql)) {
                    } else {
                        // Handle MySQL error
                        //
                        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                    }
                    // Bye bye
                    mysqli_close($conn);                   
                }
            }
        ?>
