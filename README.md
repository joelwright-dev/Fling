# Fling

Fling is an open source search engine. Fling uses it's very own web crawler to index the web.

We've all had our fair share of search engines and the terror they can be to use. Who knows what Google is doing with all that data! We need to take back the World Wide Web and break free of tailored search results which appeal to you for corporations to make bank.

## Installation

Install the latest version of [php8.0](https://www.php.net/downloads.php).

### Ubuntu/Debian distributions

```bash
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php

sudo apt update
sudo apt install php8.0 libapache2-mod-php8.0

sudo systemctl restart apache2
```

Install mbstring for PHP8.0

```bash
sudo apt-get install php8.0-mbstring
```

Install DOMDocument for PHP8.0

```bash
sudo apt-get install php8.0-dom
```

Run the webcrawler.php script

```bash
php webcrawler.php
```

Ensure you edit your database credentials in the php file. If you wish to crawl a different URL, change the default URL in the tocrawl.txt file.

## Contributing

Fling at this current stage is not live and cannot be used as a replacement search engine. For now, stay tuned. If you can't do this then please, make a pull request with your changes to accelerate the development of Fling. For major changes, please open an issue first to discuss what you would like to change. Please make sure to update tests as appropriate.

I am currently working on a system where multiple people can contribute to the crawling and indexing of sites. I plan on doing this by hosting the queue and database on a web server where participants can sign up and use their computer to index the web. Your name might just be included as a contributer to Fling.

## License
[MIT](https://choosealicense.com/licenses/mit/)
