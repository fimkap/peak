FROM node:latest as react-client

# Create app directory
WORKDIR /app

COPY . ./

RUN yarn install
RUN yarn build

EXPOSE 3001
CMD ["yarn", "start"]
