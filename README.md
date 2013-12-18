serpcoin
===

Command line tools for physical bitcoin storage. 

===

__INCLUDES__

Included in this release is a copy of PHP QR Code software which is licensed under the LGPL 3
You can view this project directly at http://phpqrcode.sourceforge.net/

===

__REQUIREMENTS__

The serpcoin scripts require the following:
  
  bash, python, python-ecdsa, php-cli, and php-gd

===

__UBUNTU PREREQUISITE INSTRUCTIONS__
  
  Newer versions of Ubuntu use:

    sudo apt-get install python-ecdsa php5-cli php5-gd

  Older versions will need to do the following:
  
    sudo apt-get install python-pip php5-cli php5-gd
    sudo pip install ecdsa

===

__USAGE__

  If all prerequisites are installed then in theory all you need to 
  do is enter the serpcoin directory and then:

    ./run.sh

  This script will ask you how many labels you want to make and then 
  create a series of files named png_front#.png and png_back#.png 
  where # starts at 1 and increments to as many pages as needed.  
  You would then use those images to print the front and back of 
  a sheet of paper then cut out the labels.

===

__QUICK START EXAMPLE__

  Here are the steps you would follow if you installed a fresh copy of 
  the latest Ubuntu server in a vm to create 25 labels.

  1. Boot up the VM
  2. Login
  3. Type the following:

```
    sudo apt-get install python-ecdsa php5-cli php5-gd unzip
    wget https://github.com/jondale/serpcoin/archive/master.zip -O serpcoin.zip
    unzip serpcoin.zip
    cd serpcoin-master
    ./run.sh
````
  4. The script asks how many labels to make.  Answer 25.
  5. Let the script finish.
  6. Print cache/page_front1.png 
  7. Take the printed out page, flip it over, feed it into the manual tray of your printer.
  8. Print cache/page_front2.png
  9. Cut and fold each label.

===

__PRINTING TIPS__

When printing out the pages you need to make sure you can set the DPI to the same
value set in the configuration file (Default: 600).  Additionally, a lot of programs
default to scaling the pages so that will need to be turned off.  I open the files
in GIMP and then when printing I set the options on the Image Options tab.

===

__CONFIGURE__

To change settings such as the pages DPI, labels sizes, version text, font, font size, 
or the logo image just change the settings in the provided serpcoin.conf file.

