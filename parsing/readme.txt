@todo Add readme about how to add Scrapy spider


export PATH="${PATH}:${HOME}/.local/bin"
scrapy runspider spiders/bober.py -o file-all.csv -t csv



Find and replace with console
sed -i 's/original/new/g' file.txt




Remove line which contains string
awk '!/Лунный календарь./' file-all.csv > tmpfile && mv tmpfile file-all.csv


"Тест:
"ТЕСТ
"ТЕСТ:
"Гороскоп потребителя и ежедневные скидки.
"Лунный календарь садовода.
"Лунный календарь на
"Лунный календарь.



prepare absolute urls instead of relative - Done

remove all bober.ru mentions


Hide .viqeo-embed class for empty div without removed iframe


https://gist.github.com/denzildoyle/31fe294065f606b4f612 - Set up metatags, OG, Facebook, twitter.


Optimize all images with mogrify
mogrify -quality "97%" -resize 2048x2048 -filter Lanczos -interlace Plane -gaussian-blur 0.05
https://stackoverflow.com/questions/7261855/recommendation-for-compressing-jpg-files-with-imagemagick


Translate taxonomy terms on EN/ES


