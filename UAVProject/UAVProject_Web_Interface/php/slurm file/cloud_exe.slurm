#!/bin/bash
#SBATCH --time=01:00:00
#SBATCH --nodes=2
#SBATCH --ntasks-per-node=4
eval `ssh-agent`
ssh-add /home/ubuntu/.ssh/uavproject.key
mpirun -x LD_PRELOAD=libmpi.so octave -q --eval "ImageProcessing ('../ImagesInfo/smallimage.png', '../ImagesInfo/smallimage_boundary.csv',52)"