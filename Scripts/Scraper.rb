# Scraper.rb: Goes to several official event sites and pulls event information to my remote database.
#
# Author: Jim Yount
#
# Credit goes to http://adrianmcli.com/2015/05/23/rails-scraping-tutorial/ for my starting code.

class ApplicationController #< ActionController::Base
  #helper :all # include all helpers, all the time
  #protect_from_forgery # See ActionController::RequestForgeryProtection for details
  
  
  # These are like includes from Java. Importing libraries.
  
  require 'mysql2'
  require 'date'
  require 'time'
  require 'open-uri'
  require 'nokogiri'
  require 'array'
  require 'sinatra'
  require 'httpclient'
  require 'open-uri'
  require 'open_uri_redirections'
  require 'certified'
  
  
  # May re-incorporate if storing in an array would help.
  # It just seems like a waste of good memory to a C coder.
  
  # Define the Event object
  #class Event
  #  def initialize(month, day)
  #    @month = month
  #    @day = day
  #  end
  #  attr_reader :month
  #  attr_reader :day
  #end
  
  
  # Attempt to scrape event data from Asheville's The Orange Peel
  
  def self.scrape_orangepeel(mysql)
  	
	# Rips the site as an html document, preserving its organizational structure
	
  	doc = Nokogiri::HTML(open("http://theorangepeel.net/events/"))
  
  
	# Searches for every instance of .event-wrap (which happens every instance of an event), 
	# and stores them into events as an array
	
  	events = doc.css('.event-wrap')
  
	
	# Executes this loop every time an event is found
	
  	events.each do |event|
	
		# Most of the important pieces of information are being ripped here 
		# by navigating through the html structure using the event objects
		# storing each event instance
		
      	month = event.css('div.event-left>div.event-date>span.month').text
      	day = event.css('div.event-left>div.event-date>span.day').text
		eventheading = event.css('div.event-details>header>span.event-heading').text
      	eventname = event.css('div.event-details>header>h3>a').text
		eventsubname = event.css('div.event-details>header>span.event-subtitle>a').text
  		eventTimeArr = event.css('div.event-details>span.event-time').text
  		eventdescription = event.css('div.event-details>span.venue-notes').to_s
  		eventcost = event.css('div.event-details>span.ticket-price').text
		eventstatus = event.css('div.event-right>div.event-status>a').text
		buylink = ''
		eventlink = event.css('div.event-details>header>h3>a')[0]['href']
		
		
		# Checks if tickets are available for purchase, and gets the buying link
		
		if eventstatus == 'Buy Tickets'
			link = event.css('div.event-right>div.event-status>a')[0]['href']
			
			if link.include? 'https'
				httpslink = link
			else
				
				# This takes a protected https redirect link from an input http link.
				# This may be redundant due to :allow_redirections => :all, which IF
				# had not installed at this point. May remove in future.
			
				httpc = HTTPClient.new
				resp = httpc.get(link)
				httpslink = resp.header['Location']
				httpslink = httpslink.join('')
				buylink = httpslink
			end
			
			# This pulls the html from the protected buy link.
			
			# Side note: open_uri_redirections was terrible to
			# install. Do not install unless you need it.
			# It can make open_uri less finicky, though.
			
			ticketCheck = Nokogiri::HTML(open(httpslink, :allow_redirections => :all))
			ticketInfo = ticketCheck.css('div.ticket-info')
			eventcost = ''
			
			
			# Pulls however many tickets there are into the cost field with line separation
			
			ticketInfo.each do |ticket|
				ticketcost = ticket.css('label>span').text + "\n"
				availability = ticket.css('label>div>span').text + "\n"
				eventcost += ticketcost + availability
			end
		elsif event.css('div.event-right>div.event-status').text.include? 'Sold Out'
			
			# This alerts users that tickets are sold out if they are.
		
			eventcost = 'SOLD OUT'
		end
			
		
		eventname += "<br/>" + eventsubname
		
		
		# This puts month abbreviation into a form that 
		# Data.parse can understand.
  
  		case month
  		when 'Jan'
  			eventdate = Date.today.year.to_s + '-1-' + day
  		when 'Feb'
  			eventdate = Date.today.year.to_s + '-2-' + day
  		when 'Mar'
  			eventdate = Date.today.year.to_s + '-3-' + day
  		when 'Apr'
  			eventdate = Date.today.year.to_s + '-4-' + day
  		when 'May'
  			eventdate = Date.today.year.to_s + '-5-' + day
  		when 'Jun'
  			eventdate = Date.today.year.to_s + '-6-' + day
  		when 'Jul'
  			eventdate = Date.today.year.to_s + '-7-' + day
  		when 'Aug'
  			eventdate = Date.today.year.to_s + '-8-' + day
  		when 'Sep'
  			eventdate = Date.today.year.to_s + '-9-' + day
  		when 'Oct'
  			eventdate = Date.today.year.to_s + '-10-' + day
  		when 'Nov'
  			eventdate = Date.today.year.to_s + '-11-' + day
  		when 'Dec'
  			eventdate = Date.today.year.to_s + '-12-' + day
  		else
  			puts 'Month out of range'
			return
  		end
		
		
		# Sorts events into categories by searching the event header for keywords
		
		eventheading = eventheading.downcase
		
		if eventheading.include? 'movie'
			eventtype = 'Movie'
		elsif eventheading.include? 'show'
			eventtype = 'Show'
		elsif eventheading.include? 'comedy'
			eventtype = 'Comedy'
		else 
			eventtype = 'Music'
		end
			
		venue = 'The Orange Peel'
		
		# This comment is a note for future string manipulation.
		
		#quote = %Q|'|
		
		
		# This gets the next digit in the string followed by the next non-space
		# characters in the string. First gets only the first instance, and join
		# joins the .scan object, which is inherently an array of results, even
		# if only one is found.
		
		eventTimeStr = eventTimeArr.scan(/(\d+)(\w+)/).first.join("")
		eventTimeStr
		
		
		# This calls the database placement method with the collected data.
		
		put_in_datbase(mysql, eventdate, eventTimeStr, venue, eventname, eventtype, eventcost, eventdescription, buylink, eventlink)
		
    end
	
  end
  
  
  # This has most of the same parts as scrape_orangepeel, but has another inner loop
  # making an entry for each showtime. They must be separate entries on my site, for
  # sorting.
  
  # Note: I am still debugging this. It does not work yet.
  
  def self.scrape_regalcinemas(mysql)
	
  	doc = Nokogiri::HTML(open("http://www.regmovies.com/theatres/theatre-folder/regal-biltmore-grande-stadium-15-rpx-8597"))
  
  	events = doc.css('div.showtime-result')
  
  	events.each do |event|
	
		eventname = event.css('div.results>div.result-info.header>div.result-left>div.title>h3>a').text
		eventlink = event.xpath('//div[@id="results"]/div[@id="result-info header"]/div[@id="result-left"]/div[@id="title"]/h3/a/@href').to_s
		#eventlink = event.css('div.results>div.result-info.header>div.result-left>div.title>h3>a')[0]['href'].to_s
		eventdate = Date.today.to_s
		eventtype = 'Movie'
		venue = 'Regal Biltmore Grande Stadium 15 & RPX'
		eventdescription = ''
		baselink = 'http://www.regmovies.com'
		eventlink = baselink + eventlink
		
		puts eventname
		puts eventlink
		
		descseek = Nokogiri::HTML(open(eventlink, :allow_redirections => :all))
		eventdescription = descseek.css('div.movie-details-area')
		
		times = event.css('ul.results-showtimes-set')
		
		times.each do |time|
		puts 'inside loop'
			buylink = time.css('div>a')[0][href]
			
			costseek = Nokogiri::HTML(open(buylink, :allow_redirections => :all))
			costtable = costseek.css('tbody.ticketTypeTable')
			eventcost = ''
			costtable.each do |row|
				puts 'inside inside loop'
				eventcost += row.css('th.ticketType').text
				eventcost += row.css('td.pricePerTicket').text
				eventcost += '</br>'
			end
			
			#eventTimeStr = time.css('div>a')[0]['data-gtm-push']
			#eventnumber = eventTimeStr.scan(/(\d+)(\w+)/).first.join("")
			#eventsuffix = eventTimeStr.scan(/(\d+)(\w+)(\w+)/).first.join("")
			#eventTimeStr = eventnumber + eventsuffix[eventnumber.length, 2]
			eventTimeStr = time.css('div>a.showtime').text
			
			put_in_datbase(mysql, eventdate, eventTimeStr, venue, eventname, eventtype, eventcost, eventdescription, buylink, eventlink)
		
		end
	end
	
  end
  
  # Puts entries into the event database.
  
  def self.put_in_datbase(mysql, eventdate, eventTimeStr, venuename, eventname, eventtype, eventcost, eventdescription, buylink, eventlink)
	eDate = Date.parse eventdate
	eventTime = Time.parse(eventTimeStr)
	
	
	# Substitutes illegal characters and escape characters, so the 
	# event title and description display correctly on the site.
		
	eventname = eventname.gsub("'", "''")
	eventdescription = eventdescription.gsub("'", "''")
	eventname = eventname.gsub("’", "''")
	eventdescription = eventdescription.gsub("’", "''")
	eventname = eventname.gsub("–", "-")
	eventdescription = eventdescription.gsub("–", "-")
		
		
	# Shows me what is being pulled while it is being pulled.
		
	puts ''
	puts eventname
	puts eDate
	puts eventTime
	puts ''
		
	
	# Place this entry into the database
	
  	mysql.query("INSERT INTO AllAshevilleEvents(date, time, venue, name, 
		eventType, cost, description, buyLink, eventLink) 
		VALUES('#{eDate}', 
		'#{eventTime}',
  		'#{venuename}', 
  		'#{eventname}',
  		'#{eventtype}',
  		'#{eventcost}',
  		'#{eventdescription}',
		'#{buylink}',
		'#{eventlink}');")
  end
  
  # Log into the database remotely
  
  mysql = Mysql2::Client.new(:host => 'yourDatabaseIP', :username => 'yourDatabaseUsername',
	:password => 'yourDatabasePassword', :database => 'yourDatabaseName')

	
  # Drop table and replace for up-to-date events
  
  mysql.query("DROP TABLE IF EXISTS AllAshevilleEvents")

  mysql.query("CREATE TABLE AllAshevilleEvents(
  id INT UNSIGNED NOT NULL AUTO_INCREMENT, 
  PRIMARY KEY (id), 
  date DATE COLLATE utf8_unicode_ci NOT NULL,
  time TIME(6) NOT NULL,
  name CHAR(50) COLLATE utf8_unicode_ci NOT NULL,
  venue CHAR(50) COLLATE utf8_unicode_ci,
  eventType CHAR(40) COLLATE utf8_unicode_ci,
  cost CHAR(100) COLLATE utf8_unicode_ci,
  description CHAR(200) COLLATE utf8_unicode_ci,
  buyLink CHAR(200) COLLATE utf8_unicode_ci,
  eventLink CHAR(200) COLLATE utf8_unicode_ci
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;")

  
  # Call all my scraper methods that fill the table
  
  scrape_regalcinemas(mysql)
  scrape_orangepeel(mysql)

  mysql.close()
end