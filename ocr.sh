#!/bin/bash

g++ -I/usr/local/include `pkg-config --cflags --libs opencv tesseract` ocr.cpp -o ocr
