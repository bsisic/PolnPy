# -*- coding: utf-8 -*-
import scrapy
from scrapy.http import Request

years = [str(x) for x in range(1996, 2019)]
# For test purpose
# years = [str(x) for x in range(1996, 1997)]
months = {
    'Jan': '01',
    'Feb': '02',
    'Mar': '03',
    'Apr': '04',
    'May': '05',
    'Jun': '06',
    'Jul': '07',
    'Aug': '08',
    'Sep': '09',
    'Oct': '10',
    'Nov': '11',
    'Dec': '12'
}

class WuSpider(scrapy.Spider):
    name = 'wu'
    allowed_domains = ['www.wunderground.com']
    start_urls = ['https://www.wunderground.com/history/airport/ELLX/2018/3/1/CustomHistory.html?/']

    def parse(self, response):
        for year in years:
            built_URL = 'https://www.wunderground.com/history/airport/ELLX/' + year + '/1/1/CustomHistory.html?dayend=31&monthend=12&yearend=' + year + '&req_city=&req_state=&req_statename=&reqdb.zip=&reqdb.magic=&reqdb.wmo='
            yield Request(built_URL, callback=self.parse_weather, meta={'Year': year})
    
    def parse_weather(self, response):
        current_year = response.meta['Year']
        wu_table = response.xpath('//table[@id="obsTable"]')
        wu_rows = wu_table.xpath('.//tbody/tr')[:]
        current_month = ''
        for row in wu_rows:
            month_cell = row.xpath('.//td/text()').extract_first()
            day_cell = row.xpath('.//td/a/text()').extract_first()
            data_cells = row.xpath('.//td/span[@class="wx-value"]/text()').extract()
            if month_cell in months:
                current_month = months[month_cell]
            else:
                events_cell = ''.join(row.xpath('.//td/text()').extract()[-1].splitlines()).strip()

                current_day = day_cell.zfill(2)
                current_date = current_year + '-' + current_month + '-' + current_day
                if len(data_cells)>16:
                    data_point = {
                        'Date': current_date,
                        'TempMax': data_cells[0],
                        'TempAvg': data_cells[1],
                        'TempMin': data_cells[2],
                        'DewPointMax': data_cells[3],
                        'DewPointAvg': data_cells[4],
                        'DewPointMin': data_cells[5],
                        'HumidMax': data_cells[6],
                        'HumidAvg': data_cells[7],
                        'HumidMin': data_cells[8],
                        'SeaLevelPressureMax': data_cells[9],
                        'SeaLevelPressureAvg': data_cells[10],
                        'SeaLevelPressureMin': data_cells[11],
                        'VisibilityMax': data_cells[12],
                        'VisibilityAvg': data_cells[13],
                        'VisibilityMin': data_cells[14],
                        'WindMax': data_cells[15],
                        'WindAvg': data_cells[16],
                        'Precip': data_cells[-1],
                        'Events': events_cell
                    }
                yield data_point
