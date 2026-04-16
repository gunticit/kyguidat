package middleware

import (
	"os"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
)

// allowedOrigins returns the set of permitted CORS origins
func allowedOrigins() map[string]bool {
	origins := map[string]bool{
		"https://khodat.com":         true,
		"https://www.khodat.com":     true,
		"https://api.khodat.com":     true,
		"https://admin.khodat.com":   true,
		"https://app.khodat.com":     true,
		"https://backend.khodat.com": true,
		"https://socket.khodat.com":  true,
	}
	// Allow extra origins from env (comma-separated) for dev/staging
	if extra := os.Getenv("CORS_EXTRA_ORIGINS"); extra != "" {
		for _, o := range strings.Split(extra, ",") {
			o = strings.TrimSpace(o)
			if o != "" {
				origins[o] = true
			}
		}
	}
	return origins
}

// CORSMiddleware handles CORS with domain whitelist
func CORSMiddleware() gin.HandlerFunc {
	origins := allowedOrigins()

	return func(c *gin.Context) {
		origin := c.Request.Header.Get("Origin")

		if origins[origin] {
			c.Writer.Header().Set("Access-Control-Allow-Origin", origin)
			c.Writer.Header().Set("Access-Control-Allow-Credentials", "true")
			c.Writer.Header().Set("Access-Control-Allow-Headers", "Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization, accept, origin, Cache-Control, X-Requested-With")
			c.Writer.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS, GET, PUT, DELETE, PATCH")
			c.Writer.Header().Set("Vary", "Origin")
		}

		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}

		// Prevent browser caching of API responses (ensures fresh data after updates)
		c.Writer.Header().Set("Cache-Control", "no-store, no-cache, must-revalidate")

		c.Next()
	}
}

// LoggerMiddleware logs requests
func LoggerMiddleware() gin.HandlerFunc {
	return func(c *gin.Context) {
		start := time.Now()

		c.Next()

		latency := time.Since(start)
		gin.DefaultWriter.Write([]byte(
			c.Request.Method + " " +
				c.Request.URL.Path + " " +
				c.ClientIP() + " " +
				latency.String() + "\n",
		))
	}
}
