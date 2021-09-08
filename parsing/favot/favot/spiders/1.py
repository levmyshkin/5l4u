import scrapy
from scrapy.spiders import SitemapSpider
import logging

class FavotSpider(scrapy.Spider):
    name = 'favot'
    allowed_domains = ['favot.ru']
    start_urls = ['https://favot.ru/post-sitemap1.xml', 'https://favot.ru/post-sitemap2.xml']
    page_count = 10


    def parse(self, response):
        response.selector.register_namespace('d', 'http://www.sitemaps.org/schemas/sitemap/0.9')

        for next_page in response.xpath('//d:loc/text()'):
            yield response.follow(next_page, self.parse)

        tags = response.css('.post-cat a::text').extract()
        tagsString = ','.join(tags)
        item = {
          'title': response.css('h1::text').extract_first('').strip(),
          'url': response.request.url,
          'tags': tagsString,
          'body': response.css('.post-content').extract_first('').replace("\r", "").replace("\n", "").strip(),
          'image': 'https://favot.ru' + response.css('.post-header .featured img::attr(src)').extract_first('').strip(),
        }

        if len(tags) != 0:
            yield item

