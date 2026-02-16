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
	reportHandler := handlers.NewReportHandler(repo)

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

	// Proxy handler for Laravel backend
	proxyHandler := handlers.NewProxyHandler(backendURL)

	// Public proxy routes (to Laravel backend)
	publicProxy := r.Group("/api/public")
	{
		publicProxy.GET("/consignments/by-slug/:slug", proxyHandler.ProxyRequest)
		publicProxy.GET("/articles", proxyHandler.ProxyRequest)
		publicProxy.GET("/articles/:slug", proxyHandler.ProxyRequest)
		publicProxy.GET("/provinces", proxyHandler.ProxyRequest)
		publicProxy.GET("/provinces/:slug/wards", proxyHandler.ProxyRequest)
	}

	// Admin routes - proxy to Laravel backend
	admin := r.Group("/api/admin")
	{
		admin.GET("/dashboard", proxyHandler.ProxyRequest)
		admin.GET("/users", proxyHandler.ProxyRequest)
		admin.DELETE("/users/:id", proxyHandler.ProxyRequest)
		admin.GET("/customers", proxyHandler.ProxyRequest)

		// Consignments - CRUD
		admin.GET("/consignments", proxyHandler.ProxyRequest)
		admin.GET("/consignments/:id", proxyHandler.ProxyRequest)
		admin.POST("/consignments", proxyHandler.ProxyRequest)
		admin.PUT("/consignments/:id", proxyHandler.ProxyRequest)
		admin.DELETE("/consignments/:id", proxyHandler.ProxyRequest)
		admin.PUT("/consignments/:id/approve", proxyHandler.ProxyRequest)
		admin.PUT("/consignments/:id/reject", proxyHandler.ProxyRequest)

		// Support Tickets
		admin.GET("/supports", proxyHandler.ProxyRequest)
		admin.GET("/supports/:id", proxyHandler.ProxyRequest)
		admin.POST("/supports/:id/reply", proxyHandler.ProxyRequest)
		admin.PUT("/supports/:id/status", proxyHandler.ProxyRequest)
		admin.POST("/supports/:id/close", proxyHandler.ProxyRequest)

		admin.GET("/transactions", proxyHandler.ProxyRequest)

		// Articles
		admin.GET("/articles", proxyHandler.ProxyRequest)
		admin.GET("/articles/:id", proxyHandler.ProxyRequest)
		admin.POST("/articles", proxyHandler.ProxyRequest)
		admin.PUT("/articles/:id", proxyHandler.ProxyRequest)
		admin.DELETE("/articles/:id", proxyHandler.ProxyRequest)

		// Administrative Divisions — Provinces
		admin.GET("/provinces", proxyHandler.ProxyRequest)
		admin.POST("/provinces", proxyHandler.ProxyRequest)
		admin.PUT("/provinces/:id", proxyHandler.ProxyRequest)
		admin.DELETE("/provinces/:id", proxyHandler.ProxyRequest)

		// Administrative Divisions — Wards
		admin.GET("/wards", proxyHandler.ProxyRequest)
		admin.POST("/wards", proxyHandler.ProxyRequest)
		admin.PUT("/wards/:id", proxyHandler.ProxyRequest)
		admin.DELETE("/wards/:id", proxyHandler.ProxyRequest)

		// Reports (handled directly by Go, not proxied)
		admin.GET("/reports/overview", reportHandler.Overview)
		admin.GET("/reports/export", reportHandler.ExportExcel)
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
