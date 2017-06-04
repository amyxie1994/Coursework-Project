#!/bin/sh
eval `ssh-agent`
ssh-add $HOME/.ssh/uavproject.key
mpirun -x LD_PRELOAD=libmpi.so octave -q --eval "ImageProcessing ('../ImagesInfo/bigimage1.tif', '../ImagesInfo/bigimage1_boundary.csv')"

