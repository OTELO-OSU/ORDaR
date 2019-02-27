<?php

$curl = curl_init();
$config = parse_ini_file('Frontend/config.ini');
$bdd      = strtolower($config['authSource']);

curl_setopt_array($curl, array(
  CURLOPT_PORT => $config['ESPORT'],
  CURLOPT_URL => 'http://'.$config['ESHOST'].'/'.$bdd,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PUT",
  CURLOPT_POSTFIELDS => "{\r\n\t\"settings\": {\r\n\"mapping.total_fields.limit\":10000,\r\n    \"analysis\": {\r\n      \"normalizer\": {\r\n        \"myLowercase\": {\r\n          \"type\": \"custom\",\r\n          \"filter\": [ \"uppercase\" ]\r\n        }\r\n      }\r\n    }\r\n  },\r\n  \"mappings\": {\r\n    \"_default_\": { \r\n     \"properties\":{\r\n      \"INTRO.FILE_CREATOR.NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t},\r\n\t\"INTRO.FILE_CREATOR.DISPLAY_NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t},\r\n\t\"INTRO.FILE_CREATOR.FIRST_NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t},\r\n\t\t\"INTRO.FILE_CREATOR.MAIL\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\"\r\n\t\t},\r\n\t \"INTRO.SCIENTIFIC_FIELD.NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t\t},\r\n\t\"INTRO.CREATION_DATE\": { \r\n      \"type\":     \"date\",\r\n      \"format\": \"yyyy-MM-dd\"\r\n\t\t},\r\n\t\"INTRO.PROJECT_NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t\t},\r\n\t\"INTRO.LANGUAGE\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t\t},\r\n\t\"INTRO.SAMPLE_KIND.NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t\t},\r\n\t\"INTRO.KEYWORDS.NAME\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t\t},\r\n\t\"INTRO.ACCESS_RIGHT\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\"\r\n\t\t\t},\r\n\t\"DATA.FILES.FILETYPE\": { \r\n      \"type\":     \"keyword\",\r\n      \"index\": \"not_analyzed\",\r\n      \"normalizer\": \"myLowercase\",\r\n       \"ignore_above\": 256\r\n\t\t\t}\r\n\t\t}\r\n    }\r\n  }\r\n}",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/json",
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
