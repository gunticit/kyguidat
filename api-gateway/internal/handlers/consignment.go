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

// Show returns single consignment
func (h *ConsignmentHandler) Show(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid ID")
		return
	}

	consignment, err := h.repo.GetConsignmentByID(uint(id))
	if err != nil {
		response.Error(c, http.StatusNotFound, "Consignment not found")
		return
	}

	response.Success(c, consignment)
}

// Categories returns all categories
func (h *ConsignmentHandler) Categories(c *gin.Context) {
	categories, err := h.repo.GetCategories()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch categories")
		return
	}

	response.Success(c, categories)
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
