echo 'host=' "$HOST_MONGO" >> /root/config.ini
echo 'port=' "$PORT_MONGO"  >> /root/config.ini
echo 'authSource=' "$BDDNAME" >> /root/config.ini
echo 'username=' "$USER_READWRITE" >> /root/config.ini
echo 'password=' "$PASSWORD_READWRITE" >> /root/config.ini


cron && tail -f /dev/null
