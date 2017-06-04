# COMP90055- Coursework-Project
#Software Development 

This is the coursework project implemented by Hongzhen XIE(773383) and Gaojie SUN().
This project implemented a crop water stress monitoring system which helps user to process thermal images to analysis water stress level of farm.

There are three steps in the analysing algorithm. First, an edge-detection will be applied over the whole image, then, for each specie, a gaussian mixture model will be built over the image of this specie. And finally, an water stress index will be built for the whole image.

In order to improve the processing speed, this algorithm has been paralleled to make it urn on two remote processing platforms.

Besides, Users can upload their thermal images to process on two platforms :Nectar and Spartan.

Also, users can manage the job they submit to remote platforms, including deleting, submitting, checking status.



