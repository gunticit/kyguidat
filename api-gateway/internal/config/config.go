package config

import (
	"fmt"
	"os"

	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

// InitDB initializes database connection
func InitDB() (*gorm.DB, error) {
	host := getEnv("DB_HOST", "mysql")
	port := getEnv("DB_PORT", "3306")
	database := getEnv("DB_DATABASE", "khodat")
	username := getEnv("DB_USERNAME", "khodat")
	password := getEnv("DB_PASSWORD", "khodat123")

	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local",
		username, password, host, port, database)

	db, err := gorm.Open(mysql.Open(dsn), &gorm.Config{
		Logger: logger.Default.LogMode(logger.Info),
	})
	if err != nil {
		return nil, err
	}

	return db, nil
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}

// GetJWTSecret returns JWT secret from environment
func GetJWTSecret() string {
	return getEnv("JWT_SECRET", "your-secret-key")
}
