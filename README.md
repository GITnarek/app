# MT Common
This service handles store registration, webhook, carrier, fulfillment services.

## Setup

### Prepare environment
Copy `.env.example` to `.env`, optionally change whatever you need

### Build containers
Run `docker-compose build`

### Get a copy of installed dependencies locally
Run `docker cp $(docker-compose ps -q mt-common-app):/var/www/html/vendor /tmp && rm -rf ./vendor && mv /tmp/vendor .`

### Launch service
Run `docker-compose up`

## Setup XDebug

Update `XDEBUG_*` variables in your .env file.

`XDEBUG_HOST` is your host local IP.

Run `docker-compose exec mt-common-app bash /docker/tools/setup_xdebug.sh`

Example for debugging a console command:
```bash
docker-compose exec --env XDEBUG_CONFIG="idekey=PHPSTORM client_host=192.168.1.130 client_port=9003 mode=debug" --env PHP_IDE_CONFIG="serverName=mt-common-app" mt-common-app php artisan mt:expose-config
```

## Custom artisan commands

tbd
