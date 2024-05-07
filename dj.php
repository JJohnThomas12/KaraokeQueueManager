<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dj Interface</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php // Handle Request from songs.php
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            $username ='z######';
            $password ='*********';
            try{ 
                $dsn = "mysql:host=courses;dbname=z######";
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $file_id = $_POST["file_id"];
                $queue_type = $_POST["queue_type"];
                $user_id = $_POST["user_id"];
                $payment_amt = $_POST["payment"];
                
                $rs = $pdo->prepare("INSERT INTO Queue (type, payment, user_id, file_id) VALUES (:type, :payment, :user_id, :file_id);");
                if(!$rs) { echo "Error in query"; die(); }
                $rs->execute(array(":type" => $queue_type, ":payment" => $payment_amt, ":user_id" => $user_id, "file_id" => $file_id));  
                echo "<p>$file_id</p>";   
                echo "<p>$queue_type</p>"; 
                echo "<p>$user_id</p>"; 
                echo "<p>$file_id</p>"; 
                echo "<p>$payment_amt</p>";            
            }
            catch(PDOexception $e){ 
                echo "Connection to database failed: " . $e->getMessage();
            }
        }
    ?>

    <h1>DJ INTERFACE</h1>
    <nav class="navbar">
        <ul>
            <li><img src="../images/home.png"></li>
            <li><a href="../index.html">Home</a></li>
            <li><img src="../images/profile.png"></li>
            <li><a href="../pages/about.html">About</a></li>
            <li><img src="../images/song.png"></li>
            <li><a href="../pages/songs.php">Songs</a></li>
            <li><img src="../images/dj.png"></li>
            <li><a href="../pages/dj.php">Dj Interface</a></li>   
        </ul>
    </nav>
    <div class="dj-middle">
        <div class="dj-middle-left">
            <div class="queue-header">
                <p>Regular Queue</p>
            </div>
            <div class ="queue-result-left">
                <?php // Regular Queue
                    $username ='z######';
                    $password ='*********'; 
                    try{ 
                        $dsn = "mysql:host=courses;dbname=z######";
                        $pdo = new PDO($dsn, $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                                    
                        $rs = $pdo->query("SELECT Queue.queue_id, User.name, User.user_id, Song.title, File.version_desc, Song.cover_image_path FROM Queue JOIN User ON Queue.user_id = User.user_id JOIN File ON Queue.file_id = File.file_id JOIN  Song ON File.song_id = Song.song_id WHERE Queue.type = 'regular' ORDER BY Queue.queue_id ASC;");
                        if(!$rs) { echo "Error in query"; die(); }
                        $p = 0;
                        while ($row = ($rs->fetch(PDO::FETCH_ASSOC)) )
                            {
                                ++$p;
                                echo "<div class ='queue-result'>";
                                echo "<p>$p</p>";
                                echo "<img class ='queue-image' src=". $row["cover_image_path"] . ">";
                                echo "<div class='overlay1'>"; // Add an overlay div
                                echo "<p class='queue-info'>Title of Song: " . $row["title"] . "</p>";
                                echo "<p class='queue-info'>Version of Song : " . $row["version_desc"] . "</p>"; 
                                echo "<p class='queue-info'>Selected User: " . $row["name"] . " (ID:". $row["user_id"] . ")</p>"; 
                                echo "</div>"; // Close overlay div
                                echo "<form method='post' id='searchForm' class='queue-choosen' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                                echo "<input type='hidden' name='queue_id' value='" . $row["queue_id"] . "'>";
                                echo "<button type='submit' class='submit submit1' >Submit</button>";
                                echo "</form>";
                                echo "</div>";
                            }
                        }
                        catch(PDOexception $e){ 
                            echo "Connection to database failed: " . $e->getMessage();
                        }
                ?>
            </div>
        </div>
        <div class="dj-middle-middle">
            <div class="enqueue-header">
                <p>Now Playing</p>
            </div>
            <div class ="enqueue-middle">
            <?php // Allows DJ to choose song from either queue
                if ($_SERVER["REQUEST_METHOD"] == "POST") 
                {
                    $username ='z######';
                    $password ='*********';
                    try{
                        $dsn = "mysql:host=courses;dbname=z######";
                        $pdo = new PDO($dsn, $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                        $queue_id = $_POST["queue_id"]; 
                        
                        $rs = $pdo->prepare("INSERT INTO Enqueue (queue_id) VALUES (:queue);");
                        if(!$rs) { echo "Error in query"; die(); }    
                        $rs->execute(array(":queue" => $queue_id));
                        
                        $rs =$pdo->query("SELECT User.user_id, User.name AS user_name, Song.title, Artist.name, 
                                         File.version_desc, Song.cover_image_path, File.file_id FROM Enqueue 
                                         JOIN Queue ON Enqueue.queue_id = Queue.queue_id 
                                         JOIN User ON Queue.user_id = User.user_id 
                                         JOIN File ON Queue.file_id = File.file_id 
                                         JOIN Song ON File.song_id = Song.song_id 
                                         JOIN Contribution ON Song.song_id = Contribution.song_id 
                                         AND Contribution.role = 'Main Artist' 
                                         JOIN Artist ON Contribution.artist_id = Artist.artist_id
                                         ORDER BY Enqueue.enqueue_id DESC;
                                        ");
                        if(!$rs) { echo "Error in query"; die(); }
                        $row = ($rs->fetch(PDO::FETCH_ASSOC));
                        echo "<img class ='enqueue-image' src=". $row["cover_image_path"] . ">";
                        echo "<div class='enqueue-info'>";
                        echo "<p>Title of Song: " . $row["title"] . "</p>";
                        echo "<p>Main Artist of Song: " . $row["name"] . "</p>";
                        echo "<p>Version of Song : " . $row["version_desc"] . "</p>"; 
                        echo "<p>File of Song (ID): " . $row["file_id"] . "</p>";
                        echo "<p>Selected User: " . $row["user_name"] . " (ID:". $row["user_id"] . ")</p>"; 
                        echo "</div>";
                    }
                    catch(PDOexception $e){ 
                        echo "Connection to database failed: " . $e->getMessage();
                    }
                }
            ?>
            </div>
        </div>
        <div class="dj-middle-right">
            <div class="queue-header">
                <p>Priority Queue</p>
            </div>
            <div class="queue-result-filter"> 
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <button type="submit" class="submit2" name="sort_queue" value="time">Sort by Time</button>
                <button type="submit" class="submit2" name="sort_queue" value="payment">Sort by Payment</button>
                </form>
            </div>
            <div class ="queue-result-right">
                <?php // Handle the Priority Queue
                    $username ='z######';
                    $password ='*********';
                            
                    try{ 
                        $dsn = "mysql:host=courses;dbname=z######";
                        $pdo = new PDO($dsn, $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                        if ( !empty($_POST["sort_queue"])) 
                        {
                            if ( $_POST["sort_queue"] == "payment")
                            {
                                $rs = $pdo->query("SELECT Queue.queue_id, User.name, User.user_id, Song.title, File.version_desc, Song.cover_image_path, Queue.payment FROM Queue JOIN User ON Queue.user_id = User.user_id JOIN File ON Queue.file_id = File.file_id JOIN  Song ON File.song_id = Song.song_id WHERE Queue.type = 'priority' ORDER BY Queue.payment DESC;");
                                if(!$rs) { echo "Error in query"; die(); }
                            }
                            else
                            {
                                $rs = $pdo->query("SELECT Queue.queue_id, User.name, User.user_id, Song.title, File.version_desc, Song.cover_image_path, Queue.payment FROM Queue JOIN User ON Queue.user_id = User.user_id JOIN File ON Queue.file_id = File.file_id JOIN  Song ON File.song_id = Song.song_id WHERE Queue.type = 'priority' ORDER BY Queue.queue_id ASC;");
                                if(!$rs) { echo "Error in query"; die(); }
                            }
                        }
                        else
                        {
                            $rs = $pdo->query("SELECT Queue.queue_id, User.name, User.user_id, Song.title, File.version_desc, Song.cover_image_path, Queue.payment FROM Queue JOIN User ON Queue.user_id = User.user_id JOIN File ON Queue.file_id = File.file_id JOIN  Song ON File.song_id = Song.song_id WHERE Queue.type = 'priority' ;");
                            if(!$rs) { echo "Error in query"; die(); }
                        }  
                        $p = 0;
                        while ($row = ($rs->fetch(PDO::FETCH_ASSOC)) )
                            {
                                ++$p;
                                echo "<div class ='queue-result'>";
                                echo "<p>$p</p>";
                                echo "<img class ='queue-image' src=". $row["cover_image_path"] . ">";
                                echo "<div class='overlay1'>"; // Add an overlay div
                                echo "<p class='queue-info'>Title of Song: " . $row["title"] . "</p>";
                                echo "<p class='queue-info'>Version of Song : " . $row["version_desc"] . "</p>"; 
                                echo "<p class='queue-info'>Selected User: " . $row["name"] . " (ID:". $row["user_id"] . ")</p>"; 
                                echo "<p class='queue-info'>User Payment: $" . $row["payment"] . "</p>"; 
                                echo "</div>"; // Close overlay div
                                echo "<form method='post' id='searchForm' class='queue-choosen' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                                echo "<input type='hidden' name='queue_id' value='" . $row["queue_id"] . "'>";
                                echo "<button type='submit' class='submit submit1' >Submit</button>";
                                echo "</form>";
                                echo "</div>";
                            }
                    }
                    catch(PDOexception $e){ 
                        echo "Connection to database failed: " . $e->getMessage();
                    }
                ?>
            
            </div>
        </div>
    </div>
</body>
</html>
