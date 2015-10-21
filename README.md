# SMS Bulk Mock test tool

## Intro

This is a mock server for HORISEN AG bulk sms API: https://www.horisen.com/en/help/api-manuals/bulk-http

## Install

After git clone do

```
composer install
cp dist.config.php config.php
touch data/log/client.log
chmod 777 data/log/client.log
cd public
bower install
```

and then update config.php with your data

You will need to install *redis* server as well

## Run

in order to run Bulk websocket server execute

```
php script/bulk-app.php
```


and then navigate to your page in `public/index.html`

## API

This tool provides an API to manage stored messages at url `public/rest.php`. These are the endpoints:

* GET */messages* get all json messages, eg. `public/rest.php/messages`
* GET */messages/:id* get particular message id
* DELETE */messages* delete all stored messages
* DELETE */messages/:id* delete particular message by id 

