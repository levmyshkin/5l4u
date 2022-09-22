import scrapy

class BoberSpider(scrapy.Spider):
    name = 'bober'
    allowed_domains = ['bober.ru']
    start_urls = ['http://bober.ru']
    page_count = 10


    def parse(self, response):
        if response.request.url[:25] == 'https://bober.ru/schedule':
            pass    
            
        if response.request.url[:29] == 'https://bober.ru/shop/product':
            pass    
            
        if response.request.url[:27] == 'https://bober.ru/user/login':
            pass      
            
        if response.request.url[:30] == 'https://bober.ru/user/register':
            pass  
            
                    
        if response.request.url[:24] == 'https://bober.ru/contact':
            pass 
            
        tags = response.css('.detailInfo__buttonBlock a::text').extract()
        tagsString = ','.join(tags)
        item = {
          'title': response.css('h1::text').extract_first('').strip(),
          'url': response.request.url,
          'tags': tagsString,
          'body': response.css('.detailInfo__detailText').extract_first('').replace("\r", "").replace("\n", "").strip(),
          'image': 'http://bober.ru' + response.css('.detailTop__detailImage img::attr(src)').extract_first('').strip(),
        }
        
        if len(tags) != 0:
            yield item       

        for next_page in response.css('a'):
            yield response.follow(next_page, self.parse)
