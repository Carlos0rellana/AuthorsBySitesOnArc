<?php
   function pullFromMain($user='',$pass=''){
    exec("git pull https://".$user.":".$pass."@github.com/Carlos0rellana/AuthorBlocks.git main");
   }

   if(isset($_GET["user"]) && isset($_GET["pass"]) ){
        pullFromMain($_GET["user"],$_GET["pass"]);
   }

?>

