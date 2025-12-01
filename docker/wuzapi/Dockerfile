FROM golang:1.24-alpine AS builder

WORKDIR /app

COPY src/go.mod src/go.sum ./
RUN go mod download

COPY src/ .
RUN go build -o wuzapi .

FROM alpine:latest

RUN apk add --no-cache ca-certificates tzdata nodejs npm

WORKDIR /app

COPY --from=builder /app/wuzapi /usr/local/bin/wuzapi
COPY --from=builder /app/migrations /usr/local/bin/migrations
COPY --from=builder /app/frontend /app/frontend
COPY --from=builder /app/static /app/static

COPY frontend.env /app/frontend/.env

RUN cd /app/frontend && npm install && npm run build

RUN adduser -D -s /bin/sh wuzapi
RUN chown -R wuzapi:wuzapi /app/frontend /app/static

EXPOSE 8080 3000

ENV TZ="America/Sao_Paulo"

CMD ["sh", "-c", "wuzapi -port=8080 -address=0.0.0.0 -admintoken=${API_KEY:-admin123456} & cd /app/frontend && npx serve -s build -l 3000"]

