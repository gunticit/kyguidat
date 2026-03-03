FROM golang:1.21-alpine AS builder

WORKDIR /app

# Install dependencies
RUN apk add --no-cache git

# Copy dependency files and download
COPY go.mod go.sum* ./
RUN go mod download || true
COPY . .
RUN go mod tidy

# Download dependencies and build (limited parallelism to prevent OOM on small VPS)
RUN CGO_ENABLED=0 GOOS=linux go build -p 2 -o main ./cmd/server

# Final stage
FROM alpine:latest

WORKDIR /app

# Copy binary from builder

COPY --from=builder /app/main .
COPY --from=builder /app/.env.example .env

# Expose port
EXPOSE 8080

# Run
CMD ["./main"]
