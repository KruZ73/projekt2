<?php
class Post {
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
        //zaczytujemy cały obraz z folderu tymczasowego do stringa
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
}

?>