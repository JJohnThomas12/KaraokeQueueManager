<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Songs</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>
<body>
<?php
include('dbcon.php')
?>
    <h1>SONGS</h1>
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
    <div class="middle-section">
        <div class="right-middle">
            <form method="post" class="search-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="search-form-input">
                    <img class="search-icon" src="../images/search.png">
                    <label form="search">Search</label>
                    <input type="text" id="getName" placeholder="Type to search...">
                    <div class="reset-button">
                        <img class="search-icon" src="../images/reset.png">
                        <button type="reset"></button>
                    </div>   
                </div>
                <div class="selectors" id="getChoice">
                    <div class="search-button">
                        <p class="button-text">All</p>
                        <input type="radio" name="choice" class="select-button" checked="checked" value="All"/>
                    </div>
                    <div class="search-button">
                        <p class="button-text">Artist</p>
                        <input type="radio" name="choice" class="select-button" value="Artist"/>
                    </div>
                    <div class="search-button">
                        <p class="button-text">Song</p>
                        <input type="radio" name="choice" class="select-button" value="Song"/>
                    </div>
                    <div class="search-button">
                        <p class="button-text">Contributor</p> 
                        <input type="radio" name="choice" class="select-button" value="Contributor"/> 
                    </div>
                </div>  
            </form>
            <div class="search-results">
                <div class="header">
                    <div class="header-item">Title</div>
                    <div class="header-item">Contributors</div>
                    <div class="header-item">Duration</div>
                </div>
                <div id="showdata">
                <?php // This is the list of songs that shows on launch of page
                    include("dbcon.php");
                    $username ='z#######';
                    $password ='*********';
                    $searchQuery = '%' . $_POST['name'] . '%';
                    try {
                        $dsn = "mysql:host=courses;dbname=z#######";
                        $pdo = new PDO($dsn, $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                        $rs = $pdo->query("SELECT File.file_id, Song.song_id,Song.cover_image_path, Song.title, Artist.name, Song.duration 
                                            FROM File 
                                            JOIN Song ON File.song_id = Song.song_id 
                                            JOIN Contribution ON File.song_id = Contribution.song_id 
                                            JOIN Artist ON Contribution.artist_id = Artist.artist_id 
                                            WHERE Contribution.role = 'Main Artist';
                                            ");
                        if(!$rs) { echo "Error in query"; die(); }
                        while($row = $rs->fetch(PDO::FETCH_ASSOC)) {
                            echo "<div class='song-result'>";
                            echo "<img src=". $row["cover_image_path"] . ">";
                            echo "<div class='title-result'>";
                            echo "<p class ='song-title'>" . $row["title"] . "<p>";
                            echo "<p class='artist-title'>" . $row["name"] . "</p>";
                            echo "</div>";
                            echo "<div class='contributor-result'>";
                            echo "<p class='contributor-title'>Hover over Current Selection</p>";
                            echo "</div>";
                            echo "<div class='duration-result'>";
                            echo "<p class='duration-title'>" . $row["duration"] . "</p>";
                            echo "</div>";
                            echo "<form method='post' id='searchForm' class='song-choosen' action=''>";
                            echo "<input type='hidden' name='file_id' value='" . $row["file_id"] . "'>";
                            echo "<button type='submit' class='submit' >Submit</button>"; // Sends info to current selection
                            echo "</form>";
                            echo "</div>";
                        }
                    } catch(PDOexception $e) {
                        echo "Connection to database failed: " . $e->getMessage();
                    }
                ?>
                </div> 
                <script>
                // This ensures the song-choosen from list appears on the
                // Current selection page
                $(document).ready(function(){
                    $('#showdata').on('submit', '.song-choosen', function(e) {
                        e.preventDefault(); // Prevent default form submission
                        var formData = $(this).serialize(); 
                        $.ajax({
                            method: 'POST',
                            '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>',
                            data: formData,
                            success: function(response) {
                                $('.middle-left').html(response); // Update the middle-left div with the response
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText); 
                            }
                        });
                    });
                });
                </script>
                <script>
                // Since the forms for the song list are submitted
                // dynamically, this is how it is done without a submit button
                $(document).ready(function(){
                    $('#getName').on("keyup", function(event){
                        event.preventDefault(); 
                        var getName = $(this).val();
                        var choice = $('.select-button:checked').val(); // Get the value of the checked radio button
                        var sortField = $('.header-item.DESC, .header-item.ASC').first().text().toLowerCase(); // Get the text of the first header item
                        var sortOrder = $('.header-item.DESC, .header-item.ASC').first().hasClass('ASC') ? 'DESC' : 'ASC'; // Get the sort order
                        sendSearchRequest(getName, choice, sortField, sortOrder);
                    });

                    $('.select-button').on("change", function(event){
                        event.preventDefault(); 
                        var choice = $(this).val();
                        var getName = $('#getName').val(); // Get the value of the input field
                        var sortField = $('.header-item.DESC, .header-item.ASC').first().text().toLowerCase(); // Get the text of the first header item
                        var sortOrder = $('.header-item.DESC, .header-item.ASC').first().hasClass('ASC') ? 'DESC' : 'ASC'; // Get the sort order
                        sendSearchRequest(getName, choice, sortField, sortOrder);
                    });

                    $('.header-item').on('click', function(event){
                        event.preventDefault(); 
                        var sortField = $(this).text().toLowerCase(); // Get the text of the clicked header item
                        var sortOrder = $(this).hasClass('ASC') ? 'DESC' : 'ASC'; // Check if the header item has 'asc' class
                        // Toggle sort order class for the clicked header item
                        $(this).toggleClass('ASC');
                        $(this).toggleClass('DESC');
                        var choice = $('.select-button:checked').val(); // Get the value of the checked radio button
                        var getName = $('#getName').val(); // Get the value of the input field
                        sendSearchRequest(getName, choice, sortField, sortOrder);
                    });
                    // This is the function that sends info over to the search.php
                    // of the user request
                    function sendSearchRequest(name, choice, field, order) {
                        $.ajax({
                            method:'POST',
                            url:'../pages/search.php',
                            data:{name: name, choice: choice, field: field, order: order},
                            success:function(response) {
                                $("#showdata").html(response); // Update the search results
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
                </script>   
            </div>
        </div>
        <div class="left-middle">
            <div class="top-left">
                <p>Current Selection</p>
            </div>
            <div class="middle-left">
                    <?php // Handles the currently selected song 
                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            $username ='z#######';
                            $password ='*********';
                        
                            try{ // if something goes wrong, an exception is thrown
                                $dsn = "mysql:host=courses;dbname=z#######";
                                $pdo = new PDO($dsn, $username, $password);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                                
                                $rs = $pdo->prepare("SELECT * FROM Song WHERE song_id = :id;");
                                if(!$rs) { echo "Error in query"; die(); }
                                $rs->execute(array(":id" => $_POST["file_id"]));
                                $row = ($rs->fetch(PDO::FETCH_ASSOC));
                                echo "<img class ='image-selected' src=". $row["cover_image_path"] . ">";
                            }
                            catch(PDOexception $e){ // handle that exception
                                echo "Connection to database failed: " . $e->getMessage();
                            }
                        }
                    ?>
                    <div class="overlay">
                        <div class="contributor-info">
                            Contributor Info
                        <?php // Handles the currently selected song with all its inf
                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                $username ='z#######';
                                $password ='*********';
                            
                                try{ 
                                    $dsn = "mysql:host=courses;dbname=z#######";
                                    $pdo = new PDO($dsn, $username, $password);
                                    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                                    
                                    $rs = $pdo->prepare("SELECT name,role FROM Contribution,Artist WHERE Contribution.artist_id = Artist.artist_id AND song_id = :id;");
                                    if(!$rs) { echo "Error in query"; die(); }
                                    $rs->execute(array(":id" => $_POST["file_id"]));
                                    while ($row = ($rs->fetch(PDO::FETCH_ASSOC)) )
                                    {
                                        echo "<div>";
                                        echo "<p>" . $row["role"] . ": " . $row["name"] . "</p>";
                                        echo "</div>";
                                    }
                                }
                                catch(PDOexception $e){ 
                                    echo "Connection to database failed: " . $e->getMessage();
                                }
                            }
                        ?>
                        </div>
                     </div>
            </div>
            <form action="../pages/dj.php" method="post">
            <div class="top-bottom-left">
                User:
                <select size = "1" name ="user_id" class="submit2">
                <?php // Ensures that only existing users can input data
                    include("library.php");
                    
                    $username ='z#######';
                    $password ='*********';

                    try{ 
                        $dsn = "mysql:host=courses;dbname=z#######";
                        $pdo = new PDO($dsn, $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

                        $rs = $pdo->query("SELECT * FROM User;");
                        if(!$rs) { echo "Error in query"; die(); }
                        while( $row = ($rs->fetch(PDO::FETCH_ASSOC)))
                        {
                            echo "<option value = ". $row["user_id"] . ">";
                            echo  $row["name"] . "(id: ". $row["user_id"] . ")";
                            echo "</option>";
                        }
                    }
                    catch(PDOexception $e){ 
                        echo "Connection to database failed: " . $e->getMessage();
                    }
                ?>
                </select>
                Payment: <input type="number" class="submit2" name="payment" min="0">
            </div>
            <div class="bottom-left">
                <input type="hidden" name="file_id" value="<?php echo $_POST["file_id"]; ?> " required>
                <button type="submit" class="submit2" name="queue_type" value="priority">Priority Queue</button>
                <button type="submit" class="submit2" name="queue_type" value="regular">Regular Queue</button>
            </form>
            </div>
        </div>
    </div>
</body>
</html>
