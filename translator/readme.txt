Installation:
1. npm install
2. npm start

Usage (see Translator.postman_collection.json):
POST request
http://localhost:8000

scrapy runspider spiders/bober.py -o file-all.csv -t csv


with x-www-form-urlencoded data:
translateText: Hello
translateFrom: en
translateTo: ru
