//
//  Copyright (c) 2013 Mr. Gecko's Media (James Coleman). http://mrgeckosmedia.com/
//
//  Permission to use, copy, modify, and/or distribute this software for any purpose
//  with or without fee is hereby granted, provided that the above copyright notice
//  and this permission notice appear in all copies.
//
//  THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
//  REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
//  FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT,
//  OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE,
//  DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS
//  ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
//

#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/imgproc/imgproc.hpp>
#include <tesseract/baseapi.h>
#include <iostream>

int main(int argc, char **argv) {
	if (argc!=2) {
		std::cerr << "Please specify an image" << std::endl;
		return -1;
	}
	
	cv::Mat image = cv::imread(argv[1]);
	if (image.empty()) {
		std::cerr << "Cannot open source image" << std::endl;
		return -1;
	}
	
	cv::Mat gray;
	cv::cvtColor(image, gray, CV_BGR2GRAY);
	
	cv::Mat final;
	cv::threshold(gray, final, 225, 255, cv::THRESH_BINARY);
	
	/*cv::namedWindow("Display window", CV_WINDOW_AUTOSIZE);
	cv::imshow("Display window", final);
	cv::waitKey(0);*/
	
	tesseract::TessBaseAPI tess;
	tess.Init(NULL, "eng", tesseract::OEM_DEFAULT);
	tess.SetPageSegMode(tesseract::PSM_SINGLE_BLOCK);
	tess.SetImage((uchar*)final.data, final.cols, final.rows, 1, final.cols);
	
	char *out = tess.GetUTF8Text();
	std::cout << out;
	
	return 0;
}