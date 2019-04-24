FROM mongo:3.4.10
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
RUN apt update ; exit 0
RUN apt install curl -y
COPY initmongo.sh initmongo.sh
RUN chmod +x initmongo.sh



