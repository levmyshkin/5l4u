import scrapy
from scrapy.spiders import SitemapSpider
import logging
from scrapy.spiders import CrawlSpider, Rule
from scrapy.linkextractors import LinkExtractor
class Spider1Spider(scrapy.Spider):
    name = 'spider1'
    start_urls = ['https://nukadeti.ru/sitemap.xml']
    page_count = 12

    # rules = (
    #     Rule(LinkExtractor(allow = 'skazki/'), callback='parse', follow=True),
    # )
    def parse(self, response):
        response.selector.register_namespace('d', 'http://www.sitemaps.org/schemas/sitemap/0.9')

        for next_page in response.xpath('//d:loc/text()'):
            yield response.follow(next_page, self.parse)

        tags = response.css('.post-cat a::text').extract()
        tagsString = ','.join(tags)
        if response.request.url.find('/skazki'):
            pass

        item = {
          'title': response.css('h1::text').extract_first('').strip(),
          'url': response.request.url,
          'description': response.css('.tale-desc::text').extract_first('').replace("\r", "").replace("\n", "").strip(),
          'body': response.css('.cnt::text').extract_first('').replace("\r", "").replace("\n", "").strip(),
          'image': 'https://nukadeti.ru/skazki/' + response.css('.tale-desc img::attr(src)').extract_first('').strip(),
        }
        

        if len(tags) != 0:
            yield item














# import scrapy


# class Spider1Spider(scrapy.Spider):
#     name = 'spider1'
#     start_urls = ['https://nukadeti.ru/skazki']

#     def parse(self, response):
#        title = response.css('title::text').extract()
#        for tales in response.css('a.cat'):
#             yield {
#                 'name': tales.css('h3::text').get(),
#                 'title': title

#             }