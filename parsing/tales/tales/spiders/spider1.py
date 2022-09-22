from site import abs_paths
import scrapy
import logging
from scrapy.spiders import SitemapSpider
import logging
from scrapy.spiders import CrawlSpider, Rule
from scrapy.linkextractors import LinkExtractor
from urllib.parse import urljoin


class Spider1Spider(scrapy.Spider):
    name = 'spider1'
    start_urls = ['https://nukadeti.ru/sitemap.xml']
#     start_urls = ['https://nukadeti.ru/skazki/khodzha-bu-ali']

    # rules = [Rule(LinkExtractor(allow = "skazki/", deny = ("raskraski/", "multiki/","chitatelskij-dnevnik/", "kratkoe-soderzhanie/","tests/",)), follow = True)]

    def parse(self, response):
        response.selector.register_namespace('d', 'http://www.sitemaps.org/schemas/sitemap/0.9')

        for next_page in response.xpath('//d:loc/text()'):
            yield response.follow(next_page, self.parse)

        tags = response.css('.tale-cats a::text').extract()
        tagsString = ','.join(tags)


        if response.css('.cont').extract_first('').strip() == '':
            teaser = response.css('.tale-text p').extract_first('').strip()
        else:
            teaser = ''

        if response.css('.tale-desc img::attr(src)').extract_first('').strip() != '':
            image = 'https://nukadeti.ru/' + response.css('.tale-desc img::attr(src)').extract_first('').strip()
        else:
            image = ''

        item = {
          'title': response.css('h1::text').extract_first('').strip(),
          'url': response.request.url,
          'body': response.css('.tale-text').extract_first('').replace("\r", "").replace("\n", "").replace("\xad", "").replace('/content/images', 'https://nukadeti.ru/content/images').strip(),
           'teaser': teaser,
          'image': image,
          'tags': tagsString
        }
        if len(tags) != 0:
            if response.request.url.find('/skazki'):
                yield item
