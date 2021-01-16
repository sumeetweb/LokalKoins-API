# LokalKoins
An API to fetch price of cryptocurrency in different fiat currencies.  
It's  a combination of Binance.com API and exchangeratesapi.io API.  
Currently it supports only 33 fiat currencies published by the European Central Bank and 363 Cryptocurrencies out of total listed in Binance.  

## Deployment
```sh
$ git clone git@github.com:sumeetweb/lokalkoins-api.git # or clone your own fork
$ cd lokalkoins-api
$ heroku create
$ git push heroku master
$ heroku open
```

or

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

## How to Fetch?
http://lokalkoins.test/?currency=USD
