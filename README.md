# Filterchi Reporter
Get filtered domains in iran from ooni.com 

## Usage
Requirement: php ^8.0

```shell
cd builds
chmod +x filterchi-report
./filterchi-report ooni:get
```
a file `ooni.csv` with filtered domain will be created. with these headers:

| domain      | filter | measurement_start_time | source |
|-------------| ------ | ---------------------- | ------ |
| twitter.com | 1 | 2023-02-13T12:54:00Z | ooni.com |

## Development
Project built using laravel-zero.
```shell
//install dependancies
composer update
//Build phar archive
php filterchi-report app:make

```

