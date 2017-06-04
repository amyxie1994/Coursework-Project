#!/bin/sh
eval `ssh-agent`
ssh-add $HOME/.ssh/uavproject.key

mpirun -x LD_PRELOAD=libmpi.so octave -q --eval "ImageProcessing ('../ImagesInfo/test.png', '../ImagesInfo/test_boundary.csv')"

