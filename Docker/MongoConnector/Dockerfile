FROM python:3.4.3
MAINTAINER ANTHONY GUIOT
ENV DEBIAN_FRONTEND noninteractive
RUN pip install 'mongo-connector[elastic5]'
COPY startup.sh /tmp/
ENTRYPOINT ["/bin/bash", "/tmp/startup.sh"]
