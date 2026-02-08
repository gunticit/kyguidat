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

	// Initialize handlers
	consignmentHandler := handlers.NewConsignmentHandler(repo)
	adminHandler := handlers.NewAdminHandler(repo)
	authHandler := handlers.NewAuthHandler(repo)

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

	// Admin routes (protected)
	admin := r.Group("/api/admin")
	admin.Use(middleware.AuthMiddleware())
	{
		admin.GET("/dashboard", adminHandler.Dashboard)
		admin.GET("/users", adminHandler.ListUsers)
		admin.GET("/consignments", adminHandler.ListConsignments)
		admin.PUT("/consignments/:id/approve", adminHandler.ApproveConsignment)
		admin.PUT("/consignments/:id/reject", adminHandler.RejectConsignment)
		admin.GET("/transactions", adminHandler.ListTransactions)
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
