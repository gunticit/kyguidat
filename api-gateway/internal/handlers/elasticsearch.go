package handlers

import (
	"net/http"

	"github.com/gin-gonic/gin"

	es "khodat/api-gateway/internal/elasticsearch"
)

// ElasticsearchHandler handles ES admin operations
type ElasticsearchHandler struct {
	syncer *es.Syncer
	client *es.Client
}

// NewElasticsearchHandler creates new handler
func NewElasticsearchHandler(client *es.Client, syncer *es.Syncer) *ElasticsearchHandler {
	return &ElasticsearchHandler{client: client, syncer: syncer}
}

// Health returns ES cluster health
func (h *ElasticsearchHandler) Health(c *gin.Context) {
	if h.client == nil {
		c.JSON(http.StatusServiceUnavailable, gin.H{
			"success": false,
			"message": "Elasticsearch not connected",
		})
		return
	}

	health, err := h.client.Health()
	if err != nil {
		c.JSON(http.StatusServiceUnavailable, gin.H{
			"success": false,
			"message": "Elasticsearch health check failed",
			"error":   err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"data":    health,
	})
}

// Sync triggers a full MySQL → ES sync
func (h *ElasticsearchHandler) Sync(c *gin.Context) {
	if h.syncer == nil {
		c.JSON(http.StatusServiceUnavailable, gin.H{
			"success": false,
			"message": "Elasticsearch syncer not available",
		})
		return
	}

	count, err := h.syncer.FullSync()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"success": false,
			"message": "Sync failed",
			"error":   err.Error(),
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{
		"success": true,
		"message": "Sync completed",
		"data": gin.H{
			"indexed_count": count,
		},
	})
}
