package handlers

import (
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"

	es "khodat/api-gateway/internal/elasticsearch"
	"khodat/api-gateway/internal/repository"
	"khodat/api-gateway/pkg/response"
)

// ConsignmentHandler handles consignment requests
type ConsignmentHandler struct {
	repo     *repository.MySQLRepository
	esClient *es.Client
}

// NewConsignmentHandler creates new handler
func NewConsignmentHandler(repo *repository.MySQLRepository, esClient *es.Client) *ConsignmentHandler {
	return &ConsignmentHandler{repo: repo, esClient: esClient}
}

// Search handles ES-powered search with all filters
func (h *ConsignmentHandler) Search(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "12"))

	params := es.SearchParams{
		Search:         c.Query("search"),
		Province:       c.Query("province"),
		District:       c.Query("district"),
		Phone:          c.Query("phone"),
		PropertyType:   c.Query("property_type"),
		HouseOnLand:    c.Query("house_on_land"),
		PriceRange:     c.Query("price_range"),
		ThoCu:          c.Query("tho_cu"),
		RoadType:       c.Query("road_type"),
		Frontage:       c.Query("frontage"),
		AreaRange:      c.Query("area_range"),
		FloorAreaRange: c.Query("floor_area_range"),
		Direction:      c.Query("direction"),
		SoTo:           c.Query("so_to"),
		SoThua:         c.Query("so_thua"),
		Sort:           c.Query("sort"),
		Page:           page,
		Limit:          limit,
	}

	// Use Elasticsearch if available
	if h.esClient != nil {
		result, err := h.esClient.SearchConsignments(params)
		if err == nil {
			// Calculate pagination meta
			lastPage := int(result.Total) / limit
			if int(result.Total)%limit > 0 {
				lastPage++
			}
			from := (page-1)*limit + 1
			to := page * limit
			if to > int(result.Total) {
				to = int(result.Total)
			}
			if result.Total == 0 {
				from = 0
				to = 0
			}

			c.JSON(http.StatusOK, gin.H{
				"success": true,
				"data":    result.Hits,
				"meta": gin.H{
					"current_page": page,
					"from":         from,
					"last_page":    lastPage,
					"per_page":     limit,
					"to":           to,
					"total":        result.Total,
				},
			})
			return
		}
		// ES failed — fallback to MySQL
	}

	// Fallback: MySQL search (original behavior)
	search := c.Query("search")
	province := c.Query("province")
	phone := c.Query("phone")

	lat, _ := strconv.ParseFloat(c.Query("lat"), 64)
	lng, _ := strconv.ParseFloat(c.Query("lng"), 64)
	maxDistance, _ := strconv.ParseFloat(c.Query("max_distance"), 64)

	consignments, total, err := h.repo.GetApprovedConsignments(page, limit, search, province, phone, lat, lng, maxDistance, nil)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch consignments")
		return
	}

	response.Paginated(c, consignments, total, page, limit)
}

// List returns approved consignments (original MySQL-based)
func (h *ConsignmentHandler) List(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "12"))
	search := c.Query("search")
	province := c.Query("province")
	phone := c.Query("phone")

	// Advanced filter params
	district := c.Query("district")
	propertyType := c.Query("property_type")
	houseOnLand := c.Query("house_on_land")
	priceRange := c.Query("price_range")
	thoCu := c.Query("tho_cu")
	roadType := c.Query("road_type")
	frontage := c.Query("frontage")
	areaRange := c.Query("area_range")
	floorAreaRange := c.Query("floor_area_range")
	direction := c.Query("direction")
	soTo := c.Query("so_to")
	soThua := c.Query("so_thua")
	sortBy := c.Query("sort")

	// Parse user location for proximity sorting
	lat, _ := strconv.ParseFloat(c.Query("lat"), 64)
	lng, _ := strconv.ParseFloat(c.Query("lng"), 64)
	maxDistance, _ := strconv.ParseFloat(c.Query("max_distance"), 64)

	filters := map[string]string{
		"district":         district,
		"property_type":    propertyType,
		"house_on_land":    houseOnLand,
		"price_range":      priceRange,
		"tho_cu":           thoCu,
		"road_type":        roadType,
		"frontage":         frontage,
		"area_range":       areaRange,
		"floor_area_range": floorAreaRange,
		"direction":        direction,
		"so_to":            soTo,
		"so_thua":          soThua,
		"sort":             sortBy,
	}

	consignments, total, err := h.repo.GetApprovedConsignments(page, limit, search, province, phone, lat, lng, maxDistance, filters)
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
