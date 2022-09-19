import scrapy
from scrapy.spiders import SitemapSpider
import logging
from scrapy.spiders import CrawlSpider, Rule
from scrapy.linkextractors import LinkExtractor
class Spider1Spider(scrapy.Spider):
    name = 'spider1'
    start_urls = ['https://nukadeti.ru/sitemap.xml']
#     start_urls = ['https://nukadeti.ru/skazki/khodzha-bu-ali']
    page_count = 12

    def parse(self, response):
        response.selector.register_namespace('d', 'http://www.sitemaps.org/schemas/sitemap/0.9')

        for next_page in response.xpath('//d:loc/text()'):
            yield response.follow(next_page, self.parse)

        tags = response.css('.tale-cats a::text').extract()
        tagsString = ','.join(tags)

        if response.request.url.find('/skazki'):
            pass

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
          'body': response.css('.tale-text').extract_first('').replace("\r", "").replace("\n", "").replace("\xad", "").strip(),
          'teaser': teaser,
          'image': image,
          'tags': tagsString
        }

        if len(tags) != 0:
            yield item
