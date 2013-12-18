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

class scPage {
    var $img;
    var $w;
    var $h;
    var $dpm;
    var $labels;
    var $pages;
    var $prefix;
    var $pad;

    function __construct($prefix){
        global $_SERPCOIN;
        $this->dpm = $_SERPCOIN["DPI"]/25.4;    
        $this->pages = 0;
        $this->prefix = $prefix;
        $this->pad = 5 * $this->dpm;
    }

    function newpage(){
        global $_SERPCOIN;
        $this->pages++;
        $this->w = $_SERPCOIN["LABEL_page_width"]*$this->dpm;
        $this->h = $_SERPCOIN["LABEL_page_height"]*$this->dpm;
        $this->img = imagecreatetruecolor( $this->w, $this->h );
        $bg = imagecolorallocate( $this->img, 255, 255, 255 );
        imagefilledrectangle( $this->img, 0, 0, $this->w, $this->h, $bg);
    }

    function add($filename){
        $this->labels[] = $filename;
    }

    function render(){
        global  $_SERPCOIN;

        $this->newpage();

        $maxX = ($_SERPCOIN["LABEL_page_width"] - $_SERPCOIN["LABEL_margin_left"] - $_SERPCOIN["LABEL_margin_right"]) * $this->dpm;
        $maxY = ($_SERPCOIN["LABEL_page_height"] - $_SERPCOIN["LABEL_margin_top"] - $_SERPCOIN["LABEL_margin_bottom"]) * $this->dpm;

        $page_top = $_SERPCOIN["LABEL_margin_top"] * $this->dpm;
        $page_bottom = $this->h - ($_SERPCOIN["LABEL_margin_top"] * $this->dpm);
        $page_left = $_SERPCOIN["LABEL_margin_left"] * $this->dpm;
        $page_right = $this->w - ($_SERPCOIN["LABEL_margin_right"] * $this->dpm);

        $x = $page_left; 
        $y = $page_top; 

        foreach($this->labels as $label){
            $img = imagecreatefromstring(file_get_contents($label));
            $w = imagesx($img);
            $h = imagesy($img);
            if ( ($w > $maxX) || ($h > $maxY)) die("Label too large for defined page size.\n");

            if ( ($x + $w + $this->pad) > $page_right ){
                $x = $page_left; 
                $y += $h + $this->pad;
            }

            if ( ($y + $h + $this->pad) > $page_bottom ){
                $this->save($this->prefix.$this->pages.".png");
                $this->newpage();
                $x = $page_left;
                $y = $page_top;   
            }

            imagecopy($this->img,$img,$x,$y,0,0,$w,$h);
            $x += $w + $this->pad;
        }
        $this->save($this->prefix.$this->pages.".png");
    }

    function renderBack(){
        global  $_SERPCOIN;

        $this->newpage();

        $maxX = ($_SERPCOIN["LABEL_page_width"] - $_SERPCOIN["LABEL_margin_left"] - $_SERPCOIN["LABEL_margin_right"]) * $this->dpm;
        $maxY = ($_SERPCOIN["LABEL_page_height"] - $_SERPCOIN["LABEL_margin_top"] - $_SERPCOIN["LABEL_margin_bottom"]) * $this->dpm;

        //$per_row = intval( ( $_SERPCOIN["LABEL_page_width"] - $_SERPCOIN["LABEL_margin_left"] - $_SERPCOIN["LABEL_margin_right"]) / $_SERPCOIN["LABEL_width"]);
        $page_top = $_SERPCOIN["LABEL_margin_top"] * $this->dpm;
        $page_bottom = $this->h - ($_SERPCOIN["LABEL_margin_top"] * $this->dpm);
        $page_left = $_SERPCOIN["LABEL_margin_left"] * $this->dpm;
        $page_right = $this->w - ($_SERPCOIN["LABEL_margin_right"] * $this->dpm); 

        $x = $page_right;
        $y = $page_top;

        $i=0;
        foreach($this->labels as $label){
            $remaining = count($this->labels) - $i;
            $i++;

            $img = imagecreatefromstring(file_get_contents($label));
            $w = imagesx($img);
            $h = imagesy($img);
            if ( ($w > $maxX) || ($h > $maxY)) die("Label too large for defined page size.\n");

            if ( ($x - $w - $this->pad) < $page_left ){
                $x = $page_right;
                $y += $h + $this->pad;
            }

            if ( ($y + $h + $this->pad) > $page_bottom ){
                $this->save($this->prefix.$this->pages.".png");
                $this->newpage();
                $x = $page_right;
                $y = $page_top;
                //if ($remaining < $per_row) $x = $page_left + ( $_SERPCOIN["LABEL_width"] * $remaining * $this->dpm);
            }

            imagecopy($this->img,$img,$x-$w,$y,0,0,$w,$h);
            $x -= $w + $this->pad;
        }
        $this->save($this->prefix.$this->pages.".png");
    }


    function save($filename){
        imagepng($this->img,$filename,9);
    }
}


if (empty($argv[1]) || !is_numeric($argv[1])) die("Usage: ".$argv[0]." <# labels>\n");

$front = new scPage("../cache/page_front");
$back = new scPage("../cache/page_back");
for($i=1; $i <= intval($argv[1]); $i++){
    $front->add("../cache/inside_label$i.png");
    $back->add("../cache/outside_label$i.png");
}
$front->render();
$back->renderBack();

?>
