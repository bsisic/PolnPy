# -*- coding: utf-8 -*-
from scrapy import Spider
from scrapy.http import Request


class PolnDataSpider(Spider):
    name = 'poln_data'
    allowed_domains = ['www.pollen.lu']
    start_urls = ['http://www.pollen.lu/index.php?qsPage=concent_arch&qsLanguage=Fra']

    def parse(self, response):
        year_URLs = response.xpath('//div[@class="content"]/table/tr/td/a/@href').extract()
        for year_URL in year_URLs:
            absolute_year_URL = response.urljoin(year_URL)
            yield Request(absolute_year_URL, callback=self.parse_year)
        # Grab the data for current year as well (separate page from archives)
        current_year_URL = response \
            .xpath('//a[text()[contains(.,"Actuelles")]]/@href').extract_first()
        absolute_current_year_URL = response.urljoin(current_year_URL)
        yield Request(absolute_current_year_URL, callback=self.parse_year)
    
    def parse_year(self, response):
        week_URLs = response.xpath('//option/@value').extract()
        for week_URL in reversed(week_URLs):
            absolute_week_URL = response.urljoin(week_URL)
            yield Request(absolute_week_URL, callback=self.parse_week)
    
        yield Request(response.url, dont_filter=True, callback=self.parse_week)

    def parse_week(self, response):
        pol_table = response.xpath("//div[@class='content']/table")

        dates_row = pol_table.xpath('.//tr')[0]
        days_cells = dates_row.xpath('.//td/text()').extract()
        days = []
        for cell in days_cells:
            days.append(cell)

        pol_list = []
        pol_data_set = {}
        pol_rows = pol_table.xpath('.//tr')[1:]
        for pol_row in pol_rows:
            pol_name = pol_row.xpath('.//td[2]/text()').extract_first()
            pol_list.append(pol_name)
            pol_data_set[pol_name] = []
            pol_cells = pol_row.xpath('.//td')[4:]
            for pol_cell in pol_cells:
                pol_data_point = pol_cell.xpath('.//font/text()').extract_first()
                pol_data_set[pol_name].append(pol_data_point)
        
        data_set = []
        for j in range(len(days)):
            data_set.append({'Date': days[j]})
            for k in range(len(pol_list)):
                data_set[j][pol_list[k]]=pol_data_set[pol_list[k]][j]

        for data_raw in data_set:
            yield data_raw
