## Development
FROM node:24.12.0-alpine AS development
WORKDIR /app
COPY . .

## Build stage
FROM node:24.12.0-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
ARG VITE_API_URL
ENV VITE_API_URL=$VITE_API_URL
ARG VITE_REVERB_APP_KEY
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME
ARG VITE_REVERB_PATH
ENV VITE_REVERB_PATH=$VITE_REVERB_PATH
RUN npm run build

## Production
FROM nginx:alpine AS production

# Copy built files from build stage
COPY --from=build /app/dist /usr/share/nginx/html

# Create Nginx configuration for SPA routing
RUN cat > /etc/nginx/conf.d/default.conf <<'EOF'
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;

    # Handle all routes - send to index.html for Vue Router
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]