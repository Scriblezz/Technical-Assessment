# React dev server container (for simplicity). For production, build and serve via nginx.
FROM node:22-alpine

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm ci || npm install

COPY . .

EXPOSE 3000

ENV NODE_ENV=development

CMD ["npm", "start"]
