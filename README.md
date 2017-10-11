# Kom Inn

This is the webapplications hosted at kom-inn.org

## Structure

The application is split into two separate parts, web and admin. They do not cross link and serve different
users. The admin parts are intended for only authorized users and is protected by HTTP Auth.

Technically, each of these two are split into a frontend (a React app written and served by NodeJS) and a
backend (a PHP webserver that provides REST JSON api endpoints).

## Install

- You need to install PHP (>7.1) and NodeJS (>6.0)
- Then each of the 4 "application" must get their dependencies installed

You may do this using `brew install php72 composer npm webpack wepback-dev-server` and `./bin/install.sh`

## Run locally

You can run and serve the entire package locally using the `./dev.sh` bash script. and then visit

- `http://localhost:8000` for the public web app, the one targeting end users
- `http://localhost:9000` for the authorized admin app

The APIs are hosted from:

- `http://localhost:8001` for the web app
- `http://localhost:9001` for the admin app

There is a [Postman](http://getpostman.com) collection in the /postman folder that lists the endpoints.
