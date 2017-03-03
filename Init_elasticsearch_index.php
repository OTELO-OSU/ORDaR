<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_PORT => "9200",
  CURLOPT_URL => "http://localhost:9200/ordar",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PUT",
  CURLOPT_POSTFIELDS => "\n{\n  \"mappings\": {\n    \"_default_\": { \n\n     \"properties\":{\n      \"INTRO.FILE_CREATOR.NAME\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t \"INTRO.SCIENTIFIC_FIELD.NAME\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t\"INTRO.CREATION_DATE\": { \n      \"type\":     \"date\",\n      \"format\": \"yyyy-MM-dd\"\n\t\t},\n\t\"INTRO.PROJECT_NAME\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t\"INTRO.LANGUAGE\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t\"INTRO.SAMPLE_KIND.NAME\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t\"INTRO.KEYWORDS.NAME\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t\"INTRO.ACCESS_RIGHT\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t},\n\t\"DATA.FILES.FILETYPE\": { \n      \"type\":     \"keyword\",\n      \"index\": \"not_analyzed\"\n\t\t\t}\n\t\t}\n    }\n  }\n}",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/json",
    "postman-token: e858ff98-08b9-5feb-7971-42447a0dc391"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
