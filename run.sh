#!/bin/bash

clear
export numlabels='none'
while [[ $numlabels != *[0-9]* ]]; do
    echo -n "How many labels do you want to make? [#] "
    read numlabels
done

cd scripts
echo -n "Generating Keys"
./MiniKey.py $numlabels > ../cache/addr.txt
echo ""
echo -n "Copying Public Bitcoin Addresses to addr.sav"
./scCopyAddress.php ../cache/addr.txt ../addr.sav
echo ""
echo -n "Creating inside of labels"
./scInsideLabel.php
echo ""
echo -n "Creating outside of labels"
./scOutsideLabel.php
echo ""
echo -n "Merging labels into pages"
./scPages.php $numlabels
echo ""
echo ""
echo "###############################################################################"
echo ""
echo "The front and back of the pages are created in the cache directory."
echo "They are named page_back#.png and page_front#.png"
echo "Be sure to print them at whatever DPI was set in serpcoin.conf"
echo ""
echo ""
echo "When you are done printing be sure to scrub the cache directory."
echo "The shell script scrub.sh is provided to help with that."
echo "You will need the application shred installed to use it."
echo ""
echo ""
echo "The public bitcoin addresses are saved to the file addr.sav"
echo "It is safe to keep and can be used for funding purposes."
echo ""
echo "###############################################################################"
echo ""
