## Development
FROM node:24.12.0-alpine AS development
WORKDIR /app
COPY . .

## Production
FROM node:24.12.0-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
ENV VITE_API_URL=https://ultralink.cloud/api
RUN npm run build

FROM httpd:alpine AS production
WORKDIR /usr/local/apache2/htdocs/
COPY --from=build /app/dist ./