#!/usr/bin/php
<?php

$from = $argv[1];
$to = $argv[2];

if (empty($from) || empty($to)) die("Usage: ".$argv[0]." <from> <to>\n");
($fp = fopen($from,"r")) || die("Unable to read $from.");
($fp_out = fopen($to,"w")) || die("Unable to write to $to.\n");

$i=0;
while ($line = fgets($fp)){
    $i++;
    list($mini,$priv,$pub) = explode(":",trim($line));
    fwrite($fp_out,"$i:$pub\n");
}

fclose($fp_out);
fclose($fp);
?>
