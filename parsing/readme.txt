@todo Add readme about how to add Scrapy spider

Create a new Spider:
scrapy startproject [name]
(Note: spider name should be different from project name).

Add settings in [project_name]/[project_name]/settings.py:
FEED_EXPORTERS = {
    'csv': 'favot.exporters.QuoteAllCsvItemExporter',
}

Add exporters.py file for CSV export.


Parsing:
export PATH="${PATH}:${HOME}/.local/bin"
scrapy runspider spiders/[spider_name].py -o file-all.csv -t csv


1.   Grab CSV
2.   Prepare CSV
3.   Import CSV - Add url
4.   Translate Terms - Add url
5.   Remove all mentions for old site - awk command
5.1. Put watermark with new site logo - Find a way
5.2. Remove this code from CSV files without removing entire line
6.   (Optional) Fix 404 errors with LinkChecker
6.1. (Optional) Create redirects from old URLs to new pages in English
7.   Register domain
8.   Set up google analytics
8.1  Set up Google Search Console
9.   Run Translator
10.  Set up Google adsense
11.  Generate site with HTTrack
12.  Deploy site on hosting (Deploy on beget-1 now)
13.  Waiting for Google Adsense verification
14.  Test Ads and Stats.



Remove this code from CSV files without removing entire line:
Find and replace with console
sed -i 's/original/new/g' file.txt

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"> - Remove
<html> -remove
</html>
<body>
</body>
detailInfo__detailWebform - remove unused classes
&nbsp; - replace with space (@todo Add in code)
replace multiple spaces with one space (@todo Check in code)



Remove line which contains string:
awk '!/Лунный календарь./' file-all.csv > tmpfile && mv tmpfile file-all.csv

"Тест:
"ТЕСТ
"ТЕСТ:
"Гороскоп потребителя и ежедневные скидки.
"Лунный календарь садовода.
"Лунный календарь на
"Лунный календарь.



prepare absolute urls instead of relative (skip - All links in content will be deleted via code)
remove all Source site mentions

html_entity_decode (skip - will be decoded via code):
&#1083;&#1077;&#1082;&#1072;&#1090;&#1077;&#1083;&#1100;&#1085;&#1099;&#1084; &#1080; &#1087;&#1088;&#1080; - change to russian chars


UA-205884857-1 - GA code
v=spf1 redirect=beget.com google-site-verification=5yDif0Hx0pHPRno55O1fmeqbrbEWfIax-J8to5P1_DY - Verify google Search console

httrack https://5l4u.com -O output_dir --disable-security-limits --max-rate=99999999999 -K3 -X -%P -wqQ%v --robots=0 -N "%h%p/%n.%t" - create HTML version of the site with HTTrack

drush book_importer:single_translate 5371 - Drush command to translate one node
drush book_importer:multiple_translate - Translate all nodes


Optimize all images with mogrify (skip - not sure it's neeeded)
mogrify -quality "97%" -resize 2048x2048 -filter Lanczos -interlace Plane -gaussian-blur 0.05
https://stackoverflow.com/questions/7261855/recommendation-for-compressing-jpg-files-with-imagemagick
