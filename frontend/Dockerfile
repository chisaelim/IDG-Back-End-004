## Development
FROM node:24.12.0-alpine AS development
WORKDIR /app
COPY . .

## Production
FROM httpd:alpine AS production
WORKDIR /usr/local/apache2/htdocs/