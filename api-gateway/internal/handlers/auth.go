package handlers

import (
	"net/http"
	"time"

	"khodat/api-gateway/internal/config"
	"khodat/api-gateway/internal/repository"
	"khodat/api-gateway/pkg/response"

	"github.com/gin-gonic/gin"
	"github.com/golang-jwt/jwt/v5"
)

type AuthHandler struct {
	repo *repository.MySQLRepository
}

func NewAuthHandler(repo *repository.MySQLRepository) *AuthHandler {
	return &AuthHandler{repo: repo}
}

type LoginRequest struct {
	Email    string `json:"email" binding:"required,email"`
	Password string `json:"password" binding:"required"`
}

// Login handles admin login
func (h *AuthHandler) Login(c *gin.Context) {
	var req LoginRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request")
		return
	}

	// Demo admin credentials - in production, verify against database
	if req.Email == "admin@khodat.com" && req.Password == "admin123" {
		// Generate JWT token
		token := jwt.NewWithClaims(jwt.SigningMethodHS256, jwt.MapClaims{
			"user_id": 1,
			"email":   req.Email,
			"role":    "admin",
			"exp":     time.Now().Add(24 * time.Hour).Unix(),
		})

		tokenString, err := token.SignedString([]byte(config.GetJWTSecret()))
		if err != nil {
			response.Error(c, http.StatusInternalServerError, "Could not generate token")
			return
		}

		response.Success(c, gin.H{
			"token": tokenString,
			"user": gin.H{
				"id":    1,
				"name":  "Admin",
				"email": req.Email,
				"role":  "admin",
			},
		})
		return
	}

	response.Error(c, http.StatusUnauthorized, "Invalid credentials")
}
