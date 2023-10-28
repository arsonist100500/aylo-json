# Description

You will need to write a program that downloads all the items in
[https://www.pornhub.com/files/json_feed_pornstars.json](https://www.pornhub.com/files/json_feed_pornstars.json) 
(the feed is updated daily) and cache images within each asset.

To make it efficient, it is desired to only call the URLs in the JSON file only once.
Demonstrate, by using a framework of your choice, your software architectural skills.
How you use the framework will be highly important in the evaluation.
How you display the feed and how many layers/pages you use is up to you,
but please ensure that we can see the complete list and the details of every item.

You will likely hit some road blocks and errors along the way,
please use your own initiative to deal with these issues, it’s part of the test.

Please ensure all code is tested before sending it back, it would be good to also see unit tests too.
The code base should be provided as a zip package or git repository url.
Ideally, the application must be deployable using Docker, otherwise we cannot guarantee the successful run of the application.

# Install & run

```
docker-compose down
docker volume create --name=postgres-aylo
docker-compose run --rm php composer install
docker-compose up -d
```
