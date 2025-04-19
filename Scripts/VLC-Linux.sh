#!/usr/bin/bash

video="/dev/video1";

if [ $# -eq 1 ]; then
	video=$1;
elif [ $# -gt 0 ]; then
	echo "\n\nusage: $0 DEV";
	exit ;
fi

cvlc --no-audio v4l2://$video --sout "#transcode{vcodec=MJPG}:standard{access=http,mux=mpjpeg,dst=:8050/stream.mjpg}" --sout-http-mime="multipart/x-mixed-replace;boundary=--7b3cc56e5f51db803f790dad720ed50a"
