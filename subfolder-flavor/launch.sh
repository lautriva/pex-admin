#!/bin/bash

OUTPUT_FOLDER='pexadmin'
COPY_OPTIONS='-R -f'

echo "Making the output folder ($OUTPUT_FOLDER)..."
mkdir $OUTPUT_FOLDER

echo "Copying Application and Framework folders..."
cp $COPY_OPTIONS ../Application $OUTPUT_FOLDER/Application
cp $COPY_OPTIONS ../Oxygen_Framework $OUTPUT_FOLDER/Oxygen_Framework

echo "Protecting them with .htaccess files to prevent public access..."
cp .htaccess $OUTPUT_FOLDER/Application
cp .htaccess $OUTPUT_FOLDER/Oxygen_Framework

echo "Moving public content..."
cp $COPY_OPTIONS ../public $OUTPUT_FOLDER

echo "Copying specific bootstrap file..."
cp -f index.php $OUTPUT_FOLDER

echo "Subfolder flavor done!"