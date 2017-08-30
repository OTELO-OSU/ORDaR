#!/bin/bash
superuser="${SUPER_USER_NAME}"
superuserpassword="${SUPER_USER_PASSWORD}"
USER_READWRITE="${USER_READWRITE}"
PASSWORD_READWRITE="${PASSWORD_READWRITE}"
USER_BACKUP="${USER_BACKUP}"
PASSWORD_BACKUP="${PASSWORD_BACKUP}"
user_doi="${user_doi}"
password_doi="${password_doi}"

if [ -e '/data/db/alreadyloaded' ]
then
    echo "Starting mongo"
    exec mongod --replSet "rs0" --keyFile /data/db/keyfile --auth  --storageEngine wiredTiger

else
    exec mongod --replSet "rs0" --keyFile /data/db/keyfile --auth  --storageEngine wiredTiger&
    openssl rand -base64 756 > /data/db/keyfile
    chmod 600 /data/db/keyfile
    echo "Creating mongo database..."
    sleep 30s

	mongo admin --eval 'rs.initiate( {
      _id: "rs0",
      version: 1,
      members: [
         { _id: 0, host : "mongodb_ordar:27017"},
 ]
}
)'
	sleep 2s
	 mongo admin --eval 'db.createUser({ user: "'$superuser'", pwd: "'$superuserpassword'", roles: [ { role: "root", db: "admin" } ] });'

	 mongo admin -u $superuser -p $superuserpassword --eval  'db.createUser({user: "'$USER_BACKUP'",pwd: "'$PASSWORD_BACKUP'",roles: [ { role: "backup", db: "admin" } ]})'

	 mongo $BDDNAME --authenticationDatabase admin -u $superuser -p $superuserpassword --eval 'db.createUser({user: "'$USER_READWRITE'",pwd: "'$PASSWORD_READWRITE'",roles: [ { role: "readWrite", db: "'$BDDNAME'" } ]})'

	 mongo $DOI_database --authenticationDatabase admin -u $superuser -p $superuserpassword --eval 'db.createUser({user: "'$user_doi'",pwd: "'$password_doi'",roles: [ { role: "readWrite", db: "'$DOI_database'" } ]})'
mongo admin -u $superuser -p $superuserpassword --eval "db.shutdownServer({timeoutSecs : 5,force : true})"
touch /data/db/alreadyloaded


fi


