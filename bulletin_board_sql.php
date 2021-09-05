<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset = "UTF-8">
    <title>
        bulletin_board_sql
    </title>
</head>

<body>
    <?php
        // DB接続設定
        $dsn = 'データベース名';
        $user = 'ユーザ名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

        // DB内にテーブル作成
        $sql = "CREATE TABLE IF NOT EXISTS bulletin" // bulletinが存在しなければbulletinを作成
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY," // "id"(ID) ... 連続した数値を自動で登録
        . "name char(32)," // "name"(名前) ... データ型 char - 文字列(32文字まで)
        . "comment TEXT," // "comment"(コメント) ... データ型 TEXT
        . "date DATETIME," // "date"(時間)　... データ型 DATETIME
        . "password char(32)" // "password" ... データ型  char -文字列(32文字まで)
        .");";
        $stmt = $pdo -> query($sql); // $sqlに登録した項目をqueryメソッドで実行

        // 行数を獲得する関数「count_row」
        function count_row(){
            global $pdo; // "$pdo" をグローバル関数として定義
            $sql = "SELECT * FROM bulletin"; // 全取得
            $stmt = $pdo -> query($sql);
            $cnt = $stmt -> rowCount(); // 行数取得
            return $cnt; 
        }

        // パスワードの正誤判定関数「is_correct」
        function is_correct($password , $id){
            global $pdo; // "$pdo" をグローバル関数として定義
            $sql = "SELECT * FROM bulletin WHERE id = :id AND password = :password";
            $stmt = $pdo -> prepare($sql);
            $stmt -> bindParam(":id" , $id , PDO::PARAM_STR);
            $stmt -> bindParam(":password" , $password , PDO::PARAM_STR);
            $stmt -> execute();
            $cnt = $stmt -> rowCount();
            if($cnt == 1){
                return true;
            }
            else{
                return false;
            }
        }

        // 入力処理
        if(isset($_POST["name"]) && isset($_POST["comment"]) && isset($_POST["password"]) && isset($_POST["re_num"])){
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $password = $_POST["password"];
            $re_num = $_POST["re_num"];
            $date = date("Y/m/d H:i:s");

            // 編集
            if($name != "" && $comment != "" && $password != "" && $re_num != ""){
                if(!(is_correct($password , $re_num))){
                    echo "パスワードが正しくありません";
                    echo "<br><br>";
                }
                else{
                    $sql = "UPDATE bulletin SET name = :name , comment = :comment , date = :date WHERE id = :id AND password = :password";
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(":name" , $name , PDO::PARAM_STR);
                    $stmt -> bindParam(":comment" , $comment , PDO::PARAM_STR);
                    $stmt -> bindParam(":date" , $date , PDO::PARAM_STR);
                    $stmt -> bindParam(":id" , $re_num , PDO::PARAM_STR);
                    $stmt -> bindParam(":password" , $password , PDO::PARAM_STR);
                    $stmt -> execute();
                    echo "$re_num" . "番の編集が完了しました";
                    echo "<br><br>";
                }
            }
            // 新規投稿
            elseif($name != "" && $comment != "" && $password != ""){
                $sql = "INSERT INTO bulletin(name , comment , date , password) VALUES(:name , :comment , :date , :password)";
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(":name" , $name , PDO::PARAM_STR);
                $stmt -> bindParam(":comment" , $comment , PDO::PARAM_STR);
                $stmt -> bindParam(":date" , $date , PDO::PARAM_STR);
                $stmt -> bindParam(":password" , $password , PDO::PARAM_STR);
                $stmt -> execute();
                echo "書き込みが完了しました";
                echo "<br><br>"; 
            }
            // エラー表示
            else{
                echo "未入力の項目があります";
                echo "<br><br>";
            }
        }
        // 削除処理
        if(isset($_POST["d_num"]) && isset($_POST["password"])){
            $d_num = $_POST["d_num"];
            $password = $_POST["password"];
            // 該当する番号が存在しない場合
            if($d_num != "" && ($d_num > count_row() || $d_num <= 0)){
                echo "入力された番号の投稿は見つかりません";
                echo "<br><br>";
            }
            // 該当する番号が存在、パスワードが入力されていない場合
            elseif($d_num != "" && $password == ""){
                echo "パスワードを入力してください";
                echo "<br><br>";
            }
            // 該当する番号が存在する場合
            elseif($d_num != "" && $password != ""){
                // パスワードが誤っている場合
                if(!(is_correct($password , $d_num))){
                    echo "パスワードが正しくありません";
                    echo "<br><br>";
                }
                // パスワードが正しい場合
                else{
                    $sql = "DELETE from bulletin WHERE id = :id AND password = :password";
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(":id" , $d_num , PDO::PARAM_STR);
                    $stmt -> bindParam(":password" , $password , PDO::PARAM_STR);
                    $stmt -> execute();

                    // 投稿番号を再度振り直す（参考）
                    $sql = "ALTER table bulletin drop column id";
                    $stmt = $pdo -> query($sql);
                    $sql = "ALTER table bulletin add id INT PRIMARY KEY AUTO_INCREMENT first";
                    $stmt = $pdo -> query($sql);

                    echo "削除が完了しました";
                    echo "<br><br>";
                }
            }
            // 番号が入力されていない場合
            else{
                echo "数字が入力されていません";
                echo "<br><br>";
            }
        }
        // 編集処理
        if(isset($_POST["e_num"]) && isset($_POST["password"])){
            $e_num = $_POST["e_num"];
            $password = $_POST["password"];
            // 該当する番号が存在しない場合
            if($e_num != "" && ($e_num > count_row() || $e_num <= 0)){
                $e_num = "";
                echo "入力された番号の投稿は存在しません";
                echo "<br><br>";
            }
            // 該当する番号が存在、パスワードが入力されていない場合
            elseif($e_num != "" && $password == ""){
                echo "パスワードを入力してください";
                echo "<br><br>";
            }
            // 該当する番号が存在する場合
            elseif($e_num != "" && $password != ""){
                // パスワードが誤っている場合
                if(!(is_correct($password , $e_num))){
                    echo "パスワードが正しくありません";
                    echo "<br><br>";
                }
                // パスワードが正しい場合
                else{
                    $sql = "SELECT id , name , comment FROM bulletin WHERE id = :id AND password = :password";
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(":id" , $e_num , PDO::PARAM_STR);
                    $stmt -> bindParam(":password" , $password , PDO::PARAM_STR);
                    $stmt -> execute();
                    $results = $stmt -> fetchAll();
                    $cnt = $stmt -> rowCount();
                    foreach($results as $result){
                        $name = $result["name"];
                        $comment = $result["comment"];
                        $e_num = $result["id"];
                    }
                    echo "$e_num" . "番を編集します";
                }
            }
            // 番号が入力されてない場合
            else{
                echo "数字が入力されていません";
                echo "<br><br>";
            }
        }
    ?>

    <h2>入力フォーム</h2>
    <form action = "" method = "post"> <!-- 入力フォーム作成 -->
        <input type = "text" name = "name" value = "<?php if(isset($name) && isset($_POST["edit"])) echo $name; ?>" placeholder = "名前"> <!-- 名前を入力 -->
        <input type = "text" name = "comment" value = "<?php if(isset($comment) && isset($_POST["edit"])) echo $comment; ?>" placeholder = "コメント"> <!-- コメントを入力 -->
        <input type = "text" name = "password" value = "" placeholder = "パスワード"> <!-- パスワードを入力 -->
        <input type = "hidden" name = "re_num" value = "<?php if(isset($e_num)) echo $e_num; ?>"> <!-- 編集番号が入力された場合、編集対象番号を入力 -->
        <input type = "submit" value = "送信">
    </form>
    <h2>削除フォーム</h2>
    <form action = "" method = "post"> <!-- 削除フォーム作成 -->
        <input type = "number" name = "d_num" placeholder = "削除対象番号"> <!-- 削除対象番号の入力 -->
        <input type = "text" name = "password" value = "" placeholder = "パスワード"> <!-- パスワードを入力 -->
        <input type = "submit" name = "delete" value = "削除"> <!-- 削除ボタン作成 -->
    </form>
    <h2>編集フォーム</h2>
    <form action = "" method = "post"> <!-- 編集フォーム作成 -->
        <input type = "number" name = "e_num" placeholder = "編集対象番号"> <!-- 編集対象番号の入力 -->
        <input type = "text" name = "password" value = "" placeholder = "パスワード"> <!-- パスワードを入力 -->
        <input type = "submit" name = "edit" value = "編集"> <!-- 編集ボタン作成 -->
    </form>
    <br>
    <br>
    <h2>掲示板</h2>

    <?php
        // ブラウザに出力
        $sql = "SELECT * FROM bulletin";
        $stmt = $pdo -> query($sql);
        $results = $stmt -> fetchAll();
        foreach($results as $row){
            echo $row["id"] . ",";
            echo $row["name"] . ",";
            echo $row["comment"] . ",";
            echo $row["date"] . ",";
            echo "<br>";
            echo "<hr>";
        }
    ?>
</body>
</html>