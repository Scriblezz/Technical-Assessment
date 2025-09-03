# Node cache service
FROM node:22-alpine

WORKDIR /usr/src/app

# Install deps first for better caching
COPY package*.json ./
COPY tsconfig.json ./
RUN npm ci || npm install

# Copy source
COPY src ./src

# Expose service port
EXPOSE 5000

ENV NODE_ENV=production

CMD ["npm", "run", "start"]
