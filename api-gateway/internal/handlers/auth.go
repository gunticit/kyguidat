package handlers

import (
	"bytes"
	"encoding/json"
	"io"
	"net/http"

	"khodat/api-gateway/pkg/response"

	"github.com/gin-gonic/gin"
)

type AuthHandler struct {
	backendURL string
}

func NewAuthHandler(backendURL string) *AuthHandler {
	return &AuthHandler{backendURL: backendURL}
}

type LoginRequest struct {
	Email    string `json:"email" binding:"required,email"`
	Password string `json:"password" binding:"required"`
}

// Login proxies auth requests to Laravel backend
func (h *AuthHandler) Login(c *gin.Context) {
	var req LoginRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request")
		return
	}

	// Proxy to Laravel backend
	jsonData, _ := json.Marshal(req)
	resp, err := http.Post(
		h.backendURL+"/api/auth/login",
		"application/json",
		bytes.NewBuffer(jsonData),
	)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Backend connection failed")
		return
	}
	defer resp.Body.Close()

	body, _ := io.ReadAll(resp.Body)

	// Forward the response from Laravel backend
	c.Data(resp.StatusCode, "application/json", body)
}
