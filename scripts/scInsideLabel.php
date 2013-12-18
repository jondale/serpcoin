#!/usr/bin/php
<?php
/*
    serpcoin - physical bitcoin storage tools.
    Copyright (C) 2013 Jondale Stratton <btc at serpco dot com> 

    serpcoin is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    serpcoin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with serpcoin.  If not, see <http://www.gnu.org/licenses/>.
*/

include("../serpcoin.conf");
include("../resources/phpqrcode.php");

class btcInsideLabel {
    var $img;
    var $w;
    var $h;
    var $dpm;
    var $pad;

    function __construct(){
        global $_SERPCOIN;
        $this->dpm = $_SERPCOIN["DPI"]/25.4;    
        $this->newLabel();
        $this->pad = $_SERPCOIN["LABEL_pad"]*$this->dpm;
    }


    function newLabel(){
        global $_SERPCOIN;

        $this->w = $_SERPCOIN["LABEL_width"]*$this->dpm;
        $this->h = $_SERPCOIN["LABEL_height"]*$this->dpm;

        $this->img = imagecreate( $this->w, $this->h );
        $bg = imagecolorallocate( $this->img, 255, 255, 255 );
        imagerectangle( $this->img, 0, 0, $this->w, $this->h, $bg); 
    }

    function putBorders(){
        $color = imagecolorallocate( $this->img, 0, 0, 0 );
        imagerectangle ( $this->img, 1, 1, $this->w-3, $this->h-3, $color );
        imageline( $this->img, $this->w/2, 1, $this->w/2, $this->h-3, $color); 
    }

    function putKey($key){
        global $_SERPCOIN;

        $color = imagecolorallocate($this->img,0,0,0);
        $lines = 3;
        $xpad = $this->w/2 + $this->pad; 
        $ypad = ($_SERPCOIN["LABEL_window_height"]*$this->dpm) + ($this->pad*4);
        $yh = $this->h - $ypad - $this->pad;
        $xw = $this->w/2 - ($this->pad*2);
        $lpl = intval(strlen($key)/$lines);
        for($i=0; $i<$lines; $i++){
            $x = $xpad;
            $y = $ypad + $this->pad + $_SERPCOIN["LABEL_FONTSIZE"] + ($yh/$lines*$i);
            $bpart = substr($key, $lpl*$i, $lpl); 
            for ($j=0; $j<strlen($bpart); $j++){
                imagettftext( $this->img, $_SERPCOIN["LABEL_FONTSIZE"], 0, $x, $y, $color, $_SERPCOIN["LABEL_FONT"], $bpart{$j}); 
                $x += $xw / strlen($bpart);
            }
        }
    }


    function putQR($key){
        ob_start();
        QRcode::png($key,false,QR_ECLEVEL_H,10,0);
        $im = imagecreatefromstring(ob_get_contents());
        ob_end_clean();
        $white = imagecolorallocate($im,255,255,255);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagecolortransparent($im,$white);
        imagecopyresampled($this->img,$im,$this->pad,$this->pad,0,0,($this->w/2)-($this->pad*2),$this->h-($this->pad*2),imagesx($im),imagesy($im));
        imagedestroy($im);
    }

    function putInfo($info){
        global $_SERPCOIN;

        $bg = imagecolorallocate($this->img,0,0,0);
        $color = imagecolorallocate($this->img,255,255,255);

        $box = imagettfbbox(  $_SERPCOIN["LABEL_FONTSIZE"], 0, $_SERPCOIN["LABEL_FONT"], $info );
        $bw = $box[2] - $box[0];
        $by = $box[1] - $box[7];

        $x = $this->w/2 + ( (($this->w/2) - $bw )/2 );
        $y = ( ($_SERPCOIN["LABEL_window_height"]*$this->dpm) + ($this->pad*2) )/2 + ( $by/2 );

        imagefilledrectangle( $this->img, $this->w/2, 1, $this->w-3, ($_SERPCOIN["LABEL_window_height"]*$this->dpm) + ($this->pad*2), $bg );
        imagettftext( $this->img, $_SERPCOIN["LABEL_FONTSIZE"], 0, $x, $y, $color, $_SERPCOIN["LABEL_FONT"], $info);
    }

    function save($filename){
        imagepng($this->img,$filename,9);
    }
}


$fp = fopen("../cache/addr.txt","r");
$i=0;
while ($line = fgets($fp)){
    $i++;
    list($mini,$priv,$pub) = explode(":",trim($line));
    $s = new btcInsideLabel();
    $s->putBorders();
    $s->putKey($mini);
    $s->putInfo($_SERPCOIN["VERSION"]." #$i");
    $s->putQR($priv);
    $s->save("../cache/inside_label$i.png");
}
fclose($fp);
?>
