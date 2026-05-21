FROM golang:1.25-alpine AS builder

WORKDIR /app

COPY src/go.mod src/go.sum ./
RUN go mod download

COPY src/ .
RUN go build -o wuzapi .

FROM alpine:latest

RUN apk add --no-cache ca-certificates tzdata

WORKDIR /app

COPY --from=builder /app/wuzapi /usr/local/bin/wuzapi
COPY --from=builder /app/static /app/static

RUN adduser -D -s /bin/sh wuzapi
RUN chown -R wuzapi:wuzapi /app/static

EXPOSE 8080

ENV TZ="America/Sao_Paulo"

CMD ["sh", "-c", "wuzapi -port=8080 -address=0.0.0.0 -admintoken=${API_KEY:-admin123456}"]
