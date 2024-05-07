<?php
include("dbcon.php");

$username ='z#######';
$password ='*********';
$searchQuery = '%' . $_POST["name"] . '%';
$searchOption = $_POST["choice"];
$searchField =  strtolower($_POST["field"]);
$searchOrder = $_POST["order"];
$orderByClause = ""; 
try {
    $dsn = "mysql:host=courses;dbname=z#######";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

    if( !empty($searchField) && !empty($searchOption))
    {
    if($searchField == "contributors") // or Artist Name since its main contributor
    {
        $orderByClause = "ORDER BY Artist.name $searchOrder";
    }
    else // either the songs title or name 
    {
        $orderByClause = "ORDER BY Song.$searchField $searchOrder";
    }
    }
    switch ($searchOption) {
        case 'Artist': // Search by Artist Name
            $rs = $pdo->prepare("SELECT File.file_id, Song.song_id,Song.cover_image_path, Song.title, Artist.name, Song.duration 
                                    FROM File 
                                    JOIN Song ON File.song_id = Song.song_id 
                                    JOIN Contribution ON File.song_id = Contribution.song_id 
                                    JOIN Artist ON Contribution.artist_id = Artist.artist_id 
                                    WHERE Contribution.role = 'Main Artist' 
                                    AND Artist.name LIKE :artist_name $orderByClause;
                                    ");
            if(!$rs) { echo "Error in query"; die(); }
            $rs->execute(array(":artist_name" => $searchQuery));
            break;
        case 'Song': // Search by Song Name
            $rs = $pdo->prepare("SELECT File.file_id, Song.song_id, Song.cover_image_path, Song.title, Artist.name, Song.duration 
                                    FROM File 
                                    JOIN Song ON File.song_id = Song.song_id 
                                    JOIN Contribution ON File.song_id = Contribution.song_id 
                                    JOIN Artist ON Contribution.artist_id = Artist.artist_id 
                                    WHERE Contribution.role = 'Main Artist' 
                                    AND Song.title LIKE :song_name $orderByClause;
                                    ");
            if(!$rs) { echo "Error in query"; die(); }
            $rs->execute(array(":song_name" => $searchQuery));
            break;
        case 'Contributor': // Search by Contributor Name
            if( !empty($searchField) && !empty($searchOption))
            {
            if($searchField == "contributors") // or Artist Name since its main contributor
            {
                $orderByClause = "ORDER BY main_artist.name $searchOrder";
            }
            else // either the songs title or name 
            {
                $orderByClause = "ORDER BY s.$searchField $searchOrder";
            }
            }
            $rs = $pdo->prepare("SELECT File.file_id, s.song_id, s.cover_image_path, s.title, main_artist.name AS main_artist, s.duration
                                FROM Song s
                                JOIN Contribution main_contribution ON s.song_id = main_contribution.song_id AND main_contribution.role = 'Main Artist'
                                JOIN Artist main_artist ON main_contribution.artist_id = main_artist.artist_id
                                JOIN File ON File.song_id = s.song_id -- Join the File table
                                WHERE s.song_id IN (
                                SELECT DISTINCT s.song_id
                                FROM Song s
                                JOIN Contribution c ON s.song_id = c.song_id
                                JOIN Artist a ON c.artist_id = a.artist_id
                                WHERE a.name LIKE :contributor_name) $orderByClause;
                                    ");
            if(!$rs) { echo "Error in query"; die(); }
            $rs->execute(array(":contributor_name" => $searchQuery));
            break;
        default: // search by Artist or Song name                          
            $rs = $pdo->prepare("SELECT File.file_id, Song.song_id, Song.cover_image_path, Song.title, Artist.name, Song.duration 
                                    FROM File 
                                    JOIN Song ON File.song_id = Song.song_id 
                                    JOIN Contribution ON File.song_id = Contribution.song_id 
                                    JOIN Artist ON Contribution.artist_id = Artist.artist_id 
                                    WHERE Contribution.role = 'Main Artist' 
                                    AND (Song.title LIKE :all_name OR Artist.Name LIKE :all_name) $orderByClause;
                                    ");
            if(!$rs) { echo "Error in query"; die(); }
            $rs->execute(array(":all_name" => $searchQuery));
            break;
    }
    
    // Check if there are any results
    if($rs->rowCount() > 0) {
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
            echo "<form method='post' class='song-choosen' action='../pages/songs.php'>";
            echo "<input type='hidden' id='searchForm' name='file_id' value='" . $row["file_id"] . "'>";
            echo "<button type='submit' class='submit'>Submit</button>"; // Sends Info back to the songs.php (current selection)
            echo "</form>";
            echo "</div>";
        }
    } else {
        // No results found, return empty response
        echo ""; 
    }
} catch(PDOexception $e) {
    echo "Connection to database failed: " . $e->getMessage();
}
?>
