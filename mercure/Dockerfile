FROM debian as mercure_hub
WORKDIR /app
COPY . ./
EXPOSE 3000
CMD ./mercure --jwt-key='!ChangeMe!' --addr=':3000' --debug --allow-anonymous --cors-allowed-origins='*' --publish-allowed-origins='http://localhost:3000'
