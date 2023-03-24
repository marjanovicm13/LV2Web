<!DOCTYPE html>
<html>
    <head>

    </head>

    <body>
        <form action="zad2.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file">
            <input type="submit" name="submit" value="Upload">
        </form>

        <button onclick="getFiles()">Dohvati podatke</button>

        <?php
            $encryption_key = md5('jed4n j4k0 v3l1k1 kljuc');
            //Odaber cipher metodu AES
            $cipher = 'AES-128-CTR';
            //Stvori IV sa ispravnom dužinom
            $iv_length = openssl_cipher_iv_length($cipher);
            $options = 0;

            $encryption_iv = random_bytes($iv_length);
        

            $db = new mysqli("localhost", "root", "", "lv2", 3307);
            if ($db->connect_error) {
                die("Connection failed: " . $db->connect_error);
            }

            $targetDir = "files/";
            $fileName = basename($_FILES["file"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

            if(isset($_POST["submit"]) && !empty($_FILES["file"]["name"])){
                $allowTypes = array('jpg','png','jpeg','pdf');
                if(in_array($fileType, $allowTypes)){
                    if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
                        $data = $fileName;
                        $data = openssl_encrypt($data , $cipher,
                        $encryption_key, $options , $encryption_iv );
                        //Spremi podatke
                        $_SESSION['podaci'] = base64_encode($data);
                        $_SESSION['iv'] = $encryption_iv;
                        //Ispiši kriptirane podatke
                        echo '<p>Kriptirani podaci su ' . base64_encode($data) .
                        '.</p>';

                        //Insert kriptiranih podataka
                        $insert = $db->query("INSERT INTO files (file) VALUES ('$data')");
                        if($insert){
                            $statusMsg = "Upload successful";
                        }else{
                            $statusMsg = "Failed";
                        } 
                    }else{
                        $statusMsg = "Error";
                    }
                }else{
                    $statusMsg = 'Invalid file format';
                }
            }else{
                $statusMsg = 'Select a file.';
            }
            echo $statusMsg;

            //Postoje li kriptirani podaci
            if (isset($_SESSION['podaci'], $_SESSION['iv'])) {
                $decryption_key = md5('jed4n j4k0 v3l1k1 kljuc');
                //Dohvati IV i kriptirane podatke
                $decryption_iv = $_SESSION['iv'];
                $dataToDecrypt= base64_decode( $_SESSION['podaci'] );
                $dataToDecrypt = openssl_decrypt($dataToDecrypt , $cipher,
                $decryption_key, $options , $decryption_iv );
                //Ispiši podatke
                echo '<p>Dekriptirani podaci su "' . trim($dataToDecrypt) . '".</p>';
            }
            else {
                echo '<p>Nema podataka.</p>';
            }
       
            ?>

    </body>
</html>

