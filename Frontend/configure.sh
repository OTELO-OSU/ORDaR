  	touch config.ini &&
  	rm config.ini 2>/dev/null &&
  	 touch config.ini &&
	echo "Please type repository name: "
	read REPOSITORY_NAME
	echo "Please type repository URL: "
	read REPOSITORY_URL
	echo "Please type upload folder: (with a slash at the end) "
	read UPLOAD_FOLDER
	echo "Please type one or more admin mail (between double quotes separated by a comma): "
	read ADMIN
	echo "Please type DATAFILE_UNIXUSER who own original data: (DATA owner) "
	read DATAFILE_UNIXUSER
	echo "Please type noreply mail: "
	read NO_REPLY_MAIL
	echo "Please type a bdd repository host: (IP OR DNS NAME)"
	read host
	echo "Please type port of bdd repository host: "
	read port
	echo "Please type the name of bdd: "
	read authSource
	echo "Please type username of bdd: "
	read username
	echo "Please type his password: "
	read password

	echo "Please type DOI PREFIX: "
	read DOI_PREFIX
	echo "Please type the name of DOI DATABASE: "
	read DOI_database
	echo "Please type the username of DOI_database: "
	read user_doi
	echo "Please type his password: "
	read password_doi
	echo "Please type DATACITE CREDENTIALS (BASIC AUTH): "
	read Auth_config_datacite

	echo "#REPOSITORY CONFIG" >> config.ini &&
	echo "REPOSITORY_NAME="$REPOSITORY_NAME >> config.ini	&&
	echo "REPOSITORY_URL="'"'$REPOSITORY_URL'"' >> config.ini	&&
	echo "UPLOAD_FOLDER="'"'$UPLOAD_FOLDER'"' >> config.ini	&&
	echo "admin[]="$ADMIN >> config.ini	&&
	echo "DATAFILE_UNIXUSER="'"'$DATAFILE_UNIXUSER'"' >> config.ini	&&
	echo "NO_REPLY_MAIL="'"'$NO_REPLY_MAIL'"' >> config.ini	&&
	echo "#REPOSITORY BDD CONFIG" >> config.ini	&&
	echo "host="$host >> config.ini	&&
	echo "port="$port >> config.ini	&&
	echo "password="$password >> config.ini	&&
	echo "authSource="$authSource >> config.ini	&&
	echo "username="$username >> config.ini	&&
	echo "password="$password >> config.ini	&&
	echo "#DOI CONFIG" >> config.ini	&&
	echo "DOI_PREFIX="$DOI_PREFIX >> config.ini	&&
	echo "DOI_database="$DOI_database >> config.ini	&&
	echo "user_doi="$user_doi >> config.ini	&&
	echo "password_doi="$password_doi >> config.ini	&&
	echo "#DATACITE CREDENTIALS" >> config.ini	&&
	echo "Auth_config_datacite="'"'$Auth_config_datacite'"' >> config.ini



