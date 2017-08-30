
#!/bin/bash

mongo="${HOST_MONGO:-mongo}"
mongoport="${PORT_MONGO:-27017}"

rm mongo-connector_config.json

echo '{
"authentication": {
"adminUsername":"'${USER_BACKUP}'",
"password": "'${PASSWORD_BACKUP}'"},
"docManagers": [
  {
    "docManager": "elastic2_doc_manager",
    "targetURL": "'${ESHOST}':'${ESPORT}'",
    "args": {
      "clientOptions": {
        "timeout": 100
      }
    }
  }
]

}' >> mongo-connector_config.json
mkdir data
sleep 2m
exec mongo-connector -m $mongo:$mongoport -c mongo-connector_config.json  --oplog-ts=/data/oplog.ts --namespace ${BDDNAME}.*


