package handlers

import (
	"io"
	"net/http"

	"github.com/gin-gonic/gin"
)

type ProxyHandler struct {
	backendURL string
}

func NewProxyHandler(backendURL string) *ProxyHandler {
	return &ProxyHandler{backendURL: backendURL}
}

// ProxyRequest forwards requests to Laravel backend with auth headers
func (h *ProxyHandler) ProxyRequest(c *gin.Context) {
	// Create new request to backend
	targetURL := h.backendURL + c.Request.URL.Path
	if c.Request.URL.RawQuery != "" {
		targetURL += "?" + c.Request.URL.RawQuery
	}

	req, err := http.NewRequest(c.Request.Method, targetURL, c.Request.Body)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create request"})
		return
	}

	// Copy headers including Authorization
	for key, values := range c.Request.Header {
		for _, value := range values {
			req.Header.Add(key, value)
		}
	}

	// Execute request - do NOT follow redirects, forward them to the client
	client := &http.Client{
		CheckRedirect: func(req *http.Request, via []*http.Request) error {
			return http.ErrUseLastResponse
		},
	}
	resp, err := client.Do(req)
	if err != nil {
		c.JSON(http.StatusBadGateway, gin.H{"error": "Backend connection failed"})
		return
	}
	defer resp.Body.Close()

	// Copy response headers
	for key, values := range resp.Header {
		for _, value := range values {
			c.Header(key, value)
		}
	}

	// Copy response body (limit to 50MB to prevent memory exhaustion)
	body, _ := io.ReadAll(io.LimitReader(resp.Body, 50*1024*1024))
	c.Data(resp.StatusCode, resp.Header.Get("Content-Type"), body)
}

// ProxyToPath returns a handler that proxies to a specific backend path (rewriting the URL)
func (h *ProxyHandler) ProxyToPath(targetPath string) gin.HandlerFunc {
	return func(c *gin.Context) {
		targetURL := h.backendURL + targetPath
		if c.Request.URL.RawQuery != "" {
			targetURL += "?" + c.Request.URL.RawQuery
		}

		req, err := http.NewRequest(c.Request.Method, targetURL, c.Request.Body)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to create request"})
			return
		}

		for key, values := range c.Request.Header {
			for _, value := range values {
				req.Header.Add(key, value)
			}
		}

		client := &http.Client{
			CheckRedirect: func(req *http.Request, via []*http.Request) error {
				return http.ErrUseLastResponse
			},
		}
		resp, err := client.Do(req)
		if err != nil {
			c.JSON(http.StatusBadGateway, gin.H{"error": "Backend connection failed"})
			return
		}
		defer resp.Body.Close()

		for key, values := range resp.Header {
			for _, value := range values {
				c.Header(key, value)
			}
		}

		body, _ := io.ReadAll(io.LimitReader(resp.Body, 50*1024*1024))
		c.Data(resp.StatusCode, resp.Header.Get("Content-Type"), body)
	}
}
