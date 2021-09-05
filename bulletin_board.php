<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charnet = "UTF-8">
    <title>
        bulletin_board
    </title>
</head>

<body>
    <?php
        touch("xxx.txt"); //ファイルが存在しない場合作成
        $filename = "xxx.txt"; //ファイル名を定義

        //パスワードの正誤を判定する関数（pass_correct）
        function pass_correct($password , $c_num){ //(入力されたパスワード , 入力された対象投稿番号)
            $filename = "xxx.txt";
            $content = file($filename , FILE_IGNORE_NEW_LINES);
            foreach($content as $content){
                list($num , $name , $comment , $date , $true_pass) = explode("<>" , $content);
                if($c_num == $num){      
                    if($true_pass == $password){
                        return true;
                    }
                }
            }
            return false;
        }
        //新規投稿か投稿内容の編集かを判断
        if(isset($_POST["name"]) && isset($_POST["comment"]) && isset($_POST["pass"]) && isset($_POST["re_num"])){
            $name = $_POST["name"]; //$nameに名前を代入
            $comment = $_POST["comment"]; //$commentにコメントを代入
            $pass = $_POST["pass"]; //$passにパスワードを代入
            $re_num = $_POST["re_num"]; //$re_numに編集対象番号を入力
            $date = date("Y/m/d H:i:s"); //$dateに現在の日時を代入
            $data = file($filename , FILE_IGNORE_NEW_LINES); //$dataに$filenameの中身を代入
            $num = count($data) + 1; //$numに$dataに含まれている要素の数+１を代入

            //名前、コメント、パスワード、編集番号が入力されている且つ、存在する投稿番号だった場合以下を実行（編集）
            if($name != "" && $comment != "" && $pass != "" && $re_num != ""){
                //パスワードが正しくない場合
                if(!(pass_correct($pass , $re_num))){
                    echo "パスワードが正しくありません";
                    echo "<br><br>";
                }
                //パスワードが正しい場合
                else{                
                    $content = file($filename , FILE_IGNORE_NEW_LINES); 
                    $file = fopen($filename , "w"); //上書き
                    foreach($content as $content){
                        list($number , $another) = explode("<>" , $content , 2);
                        if($number == $re_num){
                            $e_str = $re_num . "<>" . $name . "<>" . $comment . "<>" . $date. "<>" . $pass;
                            fwrite($file , $e_str . PHP_EOL);
                        }
                        else{
                            fwrite($file , $content . PHP_EOL);
                        }
                    }
                    fclose($file);
                    echo "編集が完了しました";
                    echo "<br><br>";
                }
            }
            //名前、コメント、パスワードのみが入力されていた場合以下を実行（新規投稿）
            elseif($name != "" && $comment != "" && $pass != ""){
                $file = fopen($filename , "a"); //追記
                $str = $num . "<>" . $name . "<>" . $comment . "<>" . $date . "<>" . $pass;
                fwrite($file , $str . PHP_EOL);
                fclose($file);
                echo "書き込みが完了しました";
                echo "<br><br>";
            }
            //未記入の項目がある場合
            else{
                echo "未記入の項目があります";
                echo "<br><br>";
            }
        }
        //削除対象番号が入力された場合以下の助理を行う（削除）
        if(isset($_POST["d_num"]) && isset($_POST["pass"])){
            $d_num = $_POST["d_num"]; //$d_numに削除対象番号を代入
            $content = file($filename , FILE_IGNORE_NEW_LINES);
            $pass = $_POST["pass"];
            //削除対象として入力された番号の投稿が存在しない場合
            if(($d_num != "") && (($d_num > count($content)) || ($d_num <= 0))){
                echo "入力された番号の投稿は見つかりません";
                echo "<br><br>";
            }
            //パスワードが入力されていない場合
            elseif($d_num != "" && $pass == ""){
                echo "パスワードを入力してください";
                echo "<br><br>";
            }
            //入力された番号と一致する投稿が存在、パスワードが入力された場合
            elseif($d_num != "" && $pass != ""){
                //パスワードが正しくない場合
                if(!(pass_correct($pass , $d_num))){
                    echo "パスワードが正しくありません";
                    echo "<br><br>";
                }
                //パスワードが正しい場合
                else{
                    $file = fopen($filename , "w"); //上書き
                    $i = 0; //投稿番号を開けないために独立した$iを用意
                    foreach($content as $content){
                        list($number , $another) = explode("<>" , $content , 2); 
                        //入力された番号と異なる投稿である場合そのまま投稿内容を反映
                        if($d_num != $number){
                            fwrite($file , ($i + 1)."<>".$another.PHP_EOL);
                            $i++; 
                        }
                    }
                    fclose($file);
                    echo "削除が完了しました";
                    echo "<br><br>";
                }
            }
            else{
                echo "数字が入力されていません";
                echo "<br><br>";
            }
        }
        //編集用動作（編集用番号が入力された場合）
        if(isset($_POST["e_num"]) && isset($_POST["pass"])){
            $e_num = $_POST["e_num"];
            $pass = $_POST["pass"];
            $content = file($filename , FILE_IGNORE_NEW_LINES);
            //編集対象として入力された番号の投稿が存在しない場合
            if(($e_num != "") && (($e_num > count($content)) || ($e_num <= 0))){
                $e_num = ""; //$e_numを初期化
                echo "入力された番号の投稿は見つかりません";
                echo "<br><br>";
            }
            //パスワードが入力されていない場合
            elseif($e_num != "" && $pass == ""){
                echo "パスワードを入力してください";
                echo "<br><br>";
            }
            //入力された番号と一致する投稿が存在、パスワードが入力された場合
            elseif($e_num != "" && $pass != ""){
                //パスワードが正しくない場合
                if(!(pass_correct($pass , $e_num))){
                    echo "パスワードが正しくありません";
                    echo "<br><br>";
                }
                //パスワードが正しい場合
                else{
                    foreach($content as $content){
                        list($num , $name , $comment , $date) = explode("<>" , $content);
                        if($e_num == $num){
                            echo "表示された投稿の編集が可能です";
                            break;
                        }
                    }
                }
            }
            else{
                echo "数字が入力されていません";
                echo "<br><br>";
            }
        } 
    ?>
    <h2>入力フォーム</h2>
    <form action = "" method = "post"> <!-- 入力フォーム作成 -->
        <input type = "text" name = "name" value = "<?php if(isset($name) && isset($_POST["edit"])) echo $name; ?>" placeholder = "名前"> <!-- 名前を入力 -->
        <input type = "text" name = "comment" value = "<?php if(isset($comment) && isset($_POST["edit"])) echo $comment; ?>"  placeholder = "コメント"> <!-- コメントを入力 -->
        <input type = "text" name = "pass" value = "" placeholder = "パスワード"> <!-- パスワードを入力 -->
        <input type = "hidden" name = "re_num" value = "<?php if(isset($e_num)) echo $e_num; ?>"> <!-- 編集番号が入力された場合、編集対象番号を入力 -->
        <input type = "submit" value = "送信"> <!-- 送信ボタン作成 -->
    </form>
    <h2>削除フォーム</h2>
    <form action = "" method = "post"> <!-- 削除フォーム作成 -->
        <input type = "number" name = "d_num" placeholder = "削除対象番号"> <!-- 削除対象番号の入力 -->
        <input type = "text" name = "pass" value = "" placeholder = "パスワード"> <!-- パスワードを入力 -->
        <input type = "submit" name = "delete" value = "削除"> <!-- 削除ボタン作成 -->
    </form>
    <h2>編集フォーム</h2>
    <form action = "" method = "post"> <!-- 編集フォーム作成 -->
        <input type = "number" name = "e_num" placeholder = "編集対象番号"> <!-- 編集対象番号の入力 -->
        <input type = "text" name = "pass" value = "" placeholder = "パスワード"> <!-- パスワードを入力 -->
        <input type = "submit" name = "edit" value = "編集"> <!-- 編集ボタン作成 -->
    </form>
    <br>

    <h2>【掲示板】</h2>

    <?php
        //ブラウザに出力
        if(file_exists($filename)){
            $contents = file($filename , FILE_IGNORE_NEW_LINES);
            foreach($contents as $contents){
                list($num , $name , $comment , $date , $pass) = explode("<>" , $contents);
                echo $num . " " . $name . " " . $comment . " " . $date;
                echo "<br>";
            }
        }
    ?>
</body>