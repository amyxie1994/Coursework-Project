#!/bin/bash
#SBATCH -p physical
#SBATCH --time=00:05:00
#SBATCH --nodes=1
#SBATCH --cpus-per-task=4
module load MATLAB/2016a
srun matlab -nodesktop -r "UAV_Project('species.csv','tinyimage.png','20','80',1);"