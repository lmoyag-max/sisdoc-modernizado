FROM node:20-alpine AS builder

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM node:20-alpine AS runner

WORKDIR /app
ENV NODE_ENV=production

# curl es requerido por el healthcheck definido en docker-compose
# (node:20-alpine no lo trae por defecto)
RUN apk add --no-cache curl

COPY package*.json ./
RUN npm ci --omit=dev
COPY --from=builder /app/dist ./dist

RUN mkdir -p uploads logs

EXPOSE 3001
CMD ["node", "dist/server.js"]
