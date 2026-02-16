package handlers

import (
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"

	"khodat/api-gateway/internal/repository"
	"khodat/api-gateway/pkg/response"
)

// ConsignmentHandler handles consignment requests
type ConsignmentHandler struct {
	repo *repository.MySQLRepository
}

// NewConsignmentHandler creates new handler
func NewConsignmentHandler(repo *repository.MySQLRepository) *ConsignmentHandler {
	return &ConsignmentHandler{repo: repo}
}

// List returns approved consignments
func (h *ConsignmentHandler) List(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "12"))
	search := c.Query("search")
	province := c.Query("province")

	// Parse user location for proximity sorting
	lat, _ := strconv.ParseFloat(c.Query("lat"), 64)
	lng, _ := strconv.ParseFloat(c.Query("lng"), 64)

	consignments, total, err := h.repo.GetApprovedConsignments(page, limit, search, province, lat, lng)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch consignments")
		return
	}

	response.Paginated(c, consignments, total, page, limit)
}

// Show returns single consignment by ID or slug
func (h *ConsignmentHandler) Show(c *gin.Context) {
	param := c.Param("id")

	// Try to parse as numeric ID first
	id, err := strconv.ParseUint(param, 10, 32)
	if err == nil {
		// Numeric ID
		consignment, err := h.repo.GetConsignmentByID(uint(id))
		if err != nil {
			response.Error(c, http.StatusNotFound, "Consignment not found")
			return
		}
		response.Success(c, consignment)
		return
	}

	// Treat as slug (seo_url)
	consignment, err := h.repo.GetConsignmentBySlug(param)
	if err != nil {
		response.Error(c, http.StatusNotFound, "Consignment not found")
		return
	}
	response.Success(c, consignment)
}

// ShowBySlug returns single consignment by seo_url slug
func (h *ConsignmentHandler) ShowBySlug(c *gin.Context) {
	slug := c.Param("slug")

	consignment, err := h.repo.GetConsignmentBySlug(slug)
	if err != nil {
		response.Error(c, http.StatusNotFound, "Consignment not found")
		return
	}

	response.Success(c, consignment)
}

// Categories returns all categories (currently empty - no categories table)
func (h *ConsignmentHandler) Categories(c *gin.Context) {
	response.Success(c, []interface{}{})
}

// Locations returns available locations
func (h *ConsignmentHandler) Locations(c *gin.Context) {
	locations, err := h.repo.GetLocations()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch locations")
		return
	}

	response.Success(c, locations)
}
