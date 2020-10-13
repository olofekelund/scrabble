## Install

`composer install`

`docker-compose up -d`


`docker-compose exec db bash`

`mysql -u root -p`

`GRANT ALL ON laravel.* TO 'laraveluser'@'%' IDENTIFIED BY 'your_laravel_db_password';`

`FLUSH PRIVILEGES;`

`EXIT;`

`exit`

`docker-compose exec app php artisan migrate`

## Endpoints

### GET localhost/api/scrabble/{id}/status
```json

```

### POST localhost/api/scrabble/newgame
```json
{
	"player1": "olof",
	"player2": "kenneth"
}
```

### POST localhost/api/scrabble/{id}/placeword
```json
{
	"player": 1,
	"word": "word",
	"direction": "vertical",
	"coordinates": {
		"x": 1,
		"y": 1
	}
}

```

### GET localhost/api/scrabble/{id}/prettyprint
```json

```
