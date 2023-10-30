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

## Installing

```shell
docker-compose down
docker volume create --name=postgres_data
docker-compose run --rm php composer install
docker-compose run --rm php /app/yii migrate --interactive=0
docker-compose up -d
```

## Feed downloading & importing

This command will start feed downloading process:
```shell
docker exec -it aylo_php /app/yii feed/download
```

After the feed is downloaded, the import process will begin automatically.
If it didn't, please run the following command to start the importing process:

```shell
docker exec -it aylo_php /app/yii feed/import
```

Items will be extracted from the feed and saved into the database.
Simultaneously, the image caching process will start (there are 16 workers for this job).
It will take some time to download all the images (thumbnails) into the local cache.

The time needed to finish all the jobs depends on the feed size and network conditions.
For the original JSON feed, which has about 21k items and 15k unique thumbnail URLs,
the estimated time is about 11 minutes.

You can use the following command to estimate the image caching progress:

```shell
docker exec -it aylo_php /app/yii queue-image-cache | egrep waiting
```


To see which jobs are run, use the following command:
```shell
docker logs aylo_php --tail 100 -f
## (Ctrl+C) to stop watching
```

## Web app

App is available on local port 8000: [http://localhost:8000/](http://localhost:8000/).
