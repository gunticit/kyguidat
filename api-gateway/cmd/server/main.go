package main

import (
	"log"
	"os"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/joho/godotenv"

	"khodat/api-gateway/internal/config"
	es "khodat/api-gateway/internal/elasticsearch"
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

	// Initialize Elasticsearch (non-fatal if unavailable)
	var esClient *es.Client
	var esSyncer *es.Syncer
	esClient, err = es.NewClient()
	if err != nil {
		log.Printf("⚠️  Elasticsearch not available: %v (search will use MySQL fallback)", err)
		esClient = nil
	} else {
		// Ensure index exists
		if err := esClient.EnsureIndex(); err != nil {
			log.Printf("⚠️  Failed to create ES index: %v", err)
		}
		esSyncer = es.NewSyncer(esClient, db)

		// Auto-sync on startup + periodic sync every 2 minutes
		go func() {
			// Wait for ES to be fully ready
			time.Sleep(5 * time.Second)

			count, err := esSyncer.FullSync()
			if err != nil {
				log.Printf("⚠️  Auto-sync failed: %v", err)
			} else {
				log.Printf("✅ Auto-synced %d consignments to Elasticsearch", count)
			}

			// Periodic sync every 2 minutes
			ticker := time.NewTicker(2 * time.Minute)
			defer ticker.Stop()
			for range ticker.C {
				count, err := esSyncer.FullSync()
				if err != nil {
					log.Printf("⚠️  Periodic sync failed: %v", err)
				} else {
					log.Printf("🔄 Periodic sync: %d consignments indexed", count)
				}
			}
		}()
	}

	consignmentHandler := handlers.NewConsignmentHandler(repo, esClient)
	reportHandler := handlers.NewReportHandler(repo)
	esHandler := handlers.NewElasticsearchHandler(esClient, esSyncer)

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
	// Proxy handler for Laravel backend
	proxyHandler := handlers.NewProxyHandler(backendURL)

	// Internal webhook for ES sync (called by Laravel after consignment changes)
	r.POST("/internal/es-sync", esHandler.Sync)

	// Public routes
	public := r.Group("/api")
	{
		public.GET("/consignments", consignmentHandler.Search)      // ES-powered search with fallback
		public.GET("/consignments-nearby", consignmentHandler.List) // Go handler for geo-sorting
		public.GET("/consignments/:id", consignmentHandler.Show)
		public.GET("/categories", consignmentHandler.Categories)
		public.GET("/locations", consignmentHandler.Locations)
	}

	// Social auth routes - proxy to Laravel backend (OAuth redirect flow)
	authProxy := r.Group("/api/auth")
	{
		authProxy.GET("/google", proxyHandler.ProxyRequest)
		authProxy.GET("/google/callback", proxyHandler.ProxyRequest)
		authProxy.GET("/facebook", proxyHandler.ProxyRequest)
		authProxy.GET("/facebook/callback", proxyHandler.ProxyRequest)
		authProxy.GET("/zalo", proxyHandler.ProxyRequest)
		authProxy.GET("/zalo/callback", proxyHandler.ProxyRequest)
	}

	// Public proxy routes (to Laravel backend)
	publicProxy := r.Group("/api/public")
	{
		publicProxy.GET("/consignments", proxyHandler.ProxyRequest) // Laravel handler for legacy/non-ES
		publicProxy.GET("/consignments/by-slug/:slug", proxyHandler.ProxyRequest)
		publicProxy.GET("/articles", proxyHandler.ProxyRequest)
		publicProxy.GET("/articles/:slug", proxyHandler.ProxyRequest)
		publicProxy.GET("/provinces", proxyHandler.ProxyRequest)
		publicProxy.GET("/provinces/:slug/wards", proxyHandler.ProxyRequest)
		publicProxy.GET("/featured-provinces", proxyHandler.ProxyRequest)
		publicProxy.GET("/pages/:slug", proxyHandler.ProxyRequest)
	}

	// Webhooks proxy
	r.POST("/api/sepay/webhook", proxyHandler.ProxyRequest)

	// Upload routes - proxy to Laravel backend (authenticated)
	uploadProxy := r.Group("/api/upload")
	{
		uploadProxy.POST("", proxyHandler.ProxyRequest)
		uploadProxy.POST("/multiple", proxyHandler.ProxyRequest)
		uploadProxy.POST("/image", proxyHandler.ProxyRequest)
		uploadProxy.POST("/image-optimized", proxyHandler.ProxyRequest)
		uploadProxy.POST("/images-optimized", proxyHandler.ProxyRequest)
		uploadProxy.POST("/base64", proxyHandler.ProxyRequest)
		uploadProxy.DELETE("", proxyHandler.ProxyRequest)
		uploadProxy.GET("/info", proxyHandler.ProxyRequest)
	}

	// Authenticated user routes - proxy to Laravel backend
	userProxy := r.Group("/api")
	{
		// Auth routes (me, forgot-password, reset-password, delete account)
		userProxy.GET("/auth/me", proxyHandler.ProxyRequest)
		userProxy.POST("/auth/forgot-password", proxyHandler.ProxyRequest)
		userProxy.POST("/auth/reset-password", proxyHandler.ProxyRequest)
		userProxy.DELETE("/auth/account", proxyHandler.ProxyRequest)
		userProxy.POST("/auth/logout", proxyHandler.ProxyRequest)
		userProxy.POST("/auth/register", proxyHandler.ProxyRequest)

		// User profile
		userProxy.GET("/user", proxyHandler.ProxyRequest)
		userProxy.GET("/user/profile", proxyHandler.ProxyRequest)
		userProxy.PUT("/user/profile", proxyHandler.ProxyRequest)
		userProxy.PUT("/user/password", proxyHandler.ProxyRequest)

		// Dashboard
		userProxy.GET("/dashboard", proxyHandler.ProxyRequest)
		userProxy.GET("/dashboard/stats", proxyHandler.ProxyRequest)
		userProxy.GET("/dashboard/recent-activities", proxyHandler.ProxyRequest)

		// User consignments (POST/PUT/DELETE - GET already registered in public group)
		userProxy.POST("/consignments", proxyHandler.ProxyRequest)
		userProxy.PUT("/consignments/:id", proxyHandler.ProxyRequest)
		userProxy.DELETE("/consignments/:id", proxyHandler.ProxyRequest)
		userProxy.POST("/consignments/:id/cancel", proxyHandler.ProxyRequest)
		userProxy.GET("/consignments/:id/history", proxyHandler.ProxyRequest)
		userProxy.POST("/consignments/:id/reactivate", proxyHandler.ProxyRequest)
		userProxy.PUT("/consignments/:id/price", proxyHandler.ProxyRequest)

		// Posting quota & packages
		userProxy.GET("/posting-quota", proxyHandler.ProxyRequest)
		userProxy.GET("/posting-packages", proxyHandler.ProxyRequest)
		userProxy.GET("/posting-packages/:id", proxyHandler.ProxyRequest)
		userProxy.POST("/posting-packages/purchase", proxyHandler.ProxyRequest)
		userProxy.GET("/my-packages", proxyHandler.ProxyRequest)
		userProxy.GET("/my-packages/current", proxyHandler.ProxyRequest)

		// Payments
		userProxy.GET("/payments", proxyHandler.ProxyRequest)
		userProxy.GET("/payments/:id", proxyHandler.ProxyRequest)
		userProxy.POST("/payments/vnpay/create", proxyHandler.ProxyRequest)
		userProxy.POST("/payments/momo/create", proxyHandler.ProxyRequest)
		userProxy.POST("/payments/bank-transfer/create", proxyHandler.ProxyRequest)
		userProxy.GET("/payments/bank-info", proxyHandler.ProxyRequest)

		// Support tickets
		userProxy.GET("/supports", proxyHandler.ProxyRequest)
		userProxy.GET("/supports/:id", proxyHandler.ProxyRequest)
		userProxy.POST("/supports", proxyHandler.ProxyRequest)
		userProxy.PUT("/supports/:id", proxyHandler.ProxyRequest)
		userProxy.DELETE("/supports/:id", proxyHandler.ProxyRequest)
		userProxy.POST("/supports/:id/messages", proxyHandler.ProxyRequest)
		userProxy.GET("/supports/:id/messages", proxyHandler.ProxyRequest)
		userProxy.POST("/supports/:id/close", proxyHandler.ProxyRequest)
		userProxy.POST("/supports/:id/reply", proxyHandler.ProxyRequest)

		// Packages & transactions (legacy routes)
		userProxy.GET("/packages", proxyHandler.ProxyRequest)
		userProxy.POST("/packages/:id/subscribe", proxyHandler.ProxyRequest)
		userProxy.GET("/transactions", proxyHandler.ProxyRequest)
	}

	// Admin routes - proxy to Laravel backend
	admin := r.Group("/api/admin")
	{
		admin.GET("/dashboard", proxyHandler.ProxyRequest)
		admin.GET("/users", proxyHandler.ProxyRequest)
		admin.PUT("/users/:id", proxyHandler.ProxyRequest)
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
		admin.POST("/consignments/:id/reactivate", proxyHandler.ProxyRequest)

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
		admin.GET("/check-slug", proxyHandler.ProxyRequest)

		// Pages
		admin.GET("/pages", proxyHandler.ProxyRequest)
		admin.GET("/pages/:id", proxyHandler.ProxyRequest)
		admin.POST("/pages", proxyHandler.ProxyRequest)
		admin.PUT("/pages/:id", proxyHandler.ProxyRequest)
		admin.DELETE("/pages/:id", proxyHandler.ProxyRequest)

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

		// Elasticsearch admin
		admin.GET("/elasticsearch/health", esHandler.Health)
		admin.POST("/elasticsearch/sync", esHandler.Sync)
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
