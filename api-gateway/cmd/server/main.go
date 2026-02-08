package main

import (
	"log"
	"os"

	"github.com/gin-gonic/gin"
	"github.com/joho/godotenv"

	"khodat/api-gateway/internal/config"
	"khodat/api-gateway/internal/handlers"
	"khodat/api-gateway/internal/middleware"
	"khodat/api-gateway/internal/repository"
)

func main() {
	// Load .env file
	if err := godotenv.Load(); err != nil {
		log.Println("No .env file found, using environment variables")
	}

	// Initialize database
	db, err := config.InitDB()
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}

	// Initialize repository
	repo := repository.NewMySQLRepository(db)

	consignmentHandler := handlers.NewConsignmentHandler(repo)

	// Backend URL for proxying auth requests
	backendURL := os.Getenv("BACKEND_URL")
	if backendURL == "" {
		backendURL = "http://backend:8000" // Default for Docker network
	}
	authHandler := handlers.NewAuthHandler(backendURL)

	// Setup Gin router
	r := gin.Default()

	// Apply middlewares
	r.Use(middleware.CORSMiddleware())
	r.Use(middleware.LoggerMiddleware())

	// Health check
	r.GET("/api/health", func(c *gin.Context) {
		c.JSON(200, gin.H{"status": "ok", "service": "api-gateway"})
	})

	// Auth routes (public)
	r.POST("/api/auth/login", authHandler.Login)

	// Public routes
	public := r.Group("/api")
	{
		public.GET("/consignments", consignmentHandler.List)
		public.GET("/consignments/:id", consignmentHandler.Show)
		public.GET("/categories", consignmentHandler.Categories)
		public.GET("/locations", consignmentHandler.Locations)
	}

	// Admin routes - proxy to Laravel backend
	proxyHandler := handlers.NewProxyHandler(backendURL)
	admin := r.Group("/api/admin")
	{
		admin.GET("/dashboard", proxyHandler.ProxyRequest)
		admin.GET("/users", proxyHandler.ProxyRequest)

		// Consignments - CRUD
		admin.GET("/consignments", proxyHandler.ProxyRequest)
		admin.GET("/consignments/:id", proxyHandler.ProxyRequest)
		admin.POST("/consignments", proxyHandler.ProxyRequest)
		admin.PUT("/consignments/:id", proxyHandler.ProxyRequest)
		admin.DELETE("/consignments/:id", proxyHandler.ProxyRequest)
		admin.PUT("/consignments/:id/approve", proxyHandler.ProxyRequest)
		admin.PUT("/consignments/:id/reject", proxyHandler.ProxyRequest)

		admin.GET("/transactions", proxyHandler.ProxyRequest)
	}

	// Get port from environment
	port := os.Getenv("PORT")
	if port == "" {
		port = "8080"
	}

	log.Printf("API Gateway starting on port %s", port)
	if err := r.Run(":" + port); err != nil {
		log.Fatalf("Failed to start server: %v", err)
	}
}
