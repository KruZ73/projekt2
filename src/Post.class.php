<?php
class Post {
    private int $id;
    private string $filename;
    private string $timestamp;
    private string $title;

    function __construct(int $i, string $f, string $t, string $title) {
        $this->id = $i;
        $this->filename = $f;
        $this->timestamp = $t;
        $this->title = $title;
    }

    // gettery

    public function getFilename() : string {
        return $this->filename;
    }

    public function getTitle() {
        return $this->title;
    }

    static function get(int $id) : Post {
        global $db;

        $q = $db->prepare("SELECT * FROM images WHERE id = ?");
        $q->bind_param('i', $id);
        $q->execute();
        $result = $q->get_result();
        $resultArray = $result->fetch_array();
        return new Post($resultArray['title'], $resultArray['filename'], $result['timestamp'], $resultArray['id']);
    }

    
    static function getLast() : Post {

        global $db;

        $query = $db->prepare("SELECT * FROM post ORDER BY timestamp DESC LIMIT 1");
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();
        $p = new Post($row['id'], $row['filename'], $row['timestamp'], $row['title']);
        return $p; 
    }


    static function getPage(int $pageNumber = 1, int $postsPerPage = 10) : array {

        global $db;

        $query = $db->prepare("SELECT * FROM images ORDER BY timestamp DESC LIMIT ? OFFSET ?");
        $offset = ($pageNumber-1) * $postsPerPage;
        $query->bind_param('ii', $postsPerPage, $offset);
        $query->execute();
        $result = $query->get_result();
        $postsArray = array();
        while($row = $result->fetch_assoc()) {
            $post = new Post($row['id'], $row['filename'], $row['timestamp'], $row['title']);
            array_push($postsArray, $post);
        }
        return $postsArray;
    }

    static function upload(string $tempFileName) {

        $targetDir = "img/";
        $imgInfo = getimagesize($tempFileName);

        if(!is_array($imgInfo)) {
            die("BŁĄD: Przekazany plik nie jest obrazem!");
        }

        $randomNumber = rand(10000, 99999) . hrtime(true);
        $hash = hash("sha256", $randomNumber);
        $newFileName = $targetDir . $hash . ".webp";

        if(file_exists($newFileName)) {
            die("BŁĄD: Podany plik już istnieje!");
        }

        $imageString = file_get_contents($tempFileName);
        $gdImage = @imagecreatefromstring($imageString);
        imagewebp($gdImage, $newFileName);

        global $db;

        $query = "INSERT images (id, timestamp, filename, title) VALUES (NULL, ?, ?, ?)";
        $preparedQ = $db->prepare($query);

        $date = date('Y-m-d H:i:s');
        $preparedQ->bind_param('sss', $date, $newFileName, $title);
        $result = $preparedQ->execute();
        
        //if (!$result) {
        //    die("Błąd bazy danych");
        //}
    }
}







    /*

    //global $db;
        //$query = $db->prepare("INSERT INTO images VALUES(NULL, ?, ?, ?)");
        //$dbTimestamp = date("Y-m-d H:i:s");
        //$query->bind_param("sss", $dbTimestamp, $newFileName, $title);
        //if(!$query->execute())
        //    die("Błąd zapisu do bazy danych");



    private string $title;
    private string $imageUrl;
    private string $timeStamp;

    function __construct(string $title, string $imageUrl, string $timeStamp) {
        $this->title = $title;
        $this->imageUrl = $imageUrl;
        $this->timeStamp = $timeStamp;
    }
    
    
    static function getLast() : Post {
        //odwołuję się do bazy danych
        global $db;
        //Przygotuj kwerendę do bazy danych
        $query = $db->prepare("SELECT * FROM post ORDER BY timestamp DESC LIMIT 1");
        //wykonaj kwerendę
        $query->execute();
        //pobierz wynik
        $result = $query->get_result();
        //przetwarzanie na tablicę asocjacyjną - bez pętli bo będzie tylko jeden
        $row = $result->fetch_assoc();
        //tworzenie obiektu
        $p = new Post($row['id'], $row['filename'], $row['timestamp']);
        //zwracanie obiektu
        return $p; 
    }
    

    static function get(int $id) : Post {
        global $db;
        $query = $db->prepare("SELECT * FROM images WHERE id = ?");
        $query->blind_param('i', $id);
        $query->execute();
        $result = $query->get_result();
        $resultArray = $result->fetch_assoc();
        return new Post($resultArray['title'],
                        $resultArray['filename'],
                        $resultArray['timestamp']);
    }


    static function getPage(int $pageNumber = 1, int $postPerPage = 10) {
        global $db;
        $query = $db->prepare("SELECT * FROM images LIMIT ? OFFSET ?");
        $offset = ($pageNumber-1) * $postPerPage;
        $query->bind_param('ii', $postPerPage, $offset);
        $query->execute();
        $result = $query->get_result();
        $postArray = array();
        while($row = $result->fetch_assoc()) {
            $post = new Post($row['title'],
                            $row['filename'],
                            $row['timestamp']);
            array_push($postsArray);
        }
        return $postArray;
    }



    static function upload(string $tempFileName, string $title = "") {
        //funkcja działa bez tworzenia instancji obiektu
        // uwaga wywołanie metodą Post::upload()
        $uploadDir = "img/";
        //sprawdź czy mamy do czynienia z obrazem
        $imgInfo = getimagesize($tempFileName);
        //jeśli plik nie jest poprawnym obrazem
        if(!is_array($imgInfo)) {
            die("BŁĄD: Przekazany plik nie jest obrazem!");
        }
        //wygeneruj _możliwie_ losowy ciąg liczbowy
        $randomSeed = rand(10000,99999) . hrtime(true);
        //wygeneruj hash, który będzie nową nazwą pliku
        $hash = hash("sha256", $randomSeed);
        //wygeneruj kompletną nazwę pliku
        $targetFileName = $uploadDir . $hash . ".webp";
        //sprawdź czy plik przypadkiem już nie istnieje
        if(file_exists($targetFileName)) {
            die("BŁĄD: Podany plik już istnieje!");
        }
        //zaczytujemy cały obraz z folderu tymczasowego do s t r inga
        $imageString = file_get_contents($tempFileName);
        //generujemy obraz jako obiekt klasy GDImage
        //@ przed nazwa funkcji powoduje zignorowanie ostrzeżeń
        $gdImage = @imagecreatefromstring($imageString);
        //zapisz plik do docelowej lokalizacji
        imagewebp($gdImage, $targetFileName);

        global $db;

        $query = $db->prepare("INSERT INTO post VALUES(NULL, ?, ?, ?)");

        $dbTimestamp = date("Y-m-d H:i:s");

        $query->bind_param("sss", $dbTimestamp, $targetFileName, $title);

        if(!$query->execute())
            die("Błąd zapisu do bazy danych");

    } 
    */


?>