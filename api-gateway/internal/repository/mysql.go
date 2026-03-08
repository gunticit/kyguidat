package repository

import (
	"fmt"
	"strconv"
	"strings"

	"khodat/api-gateway/internal/models"

	"gorm.io/gorm"
)

// Repository interface for database operations
type Repository interface {
	// Consignments
	GetApprovedConsignments(page, limit int, search, province, phone string, lat, lng, maxDistance float64, filters map[string]string) ([]models.Consignment, int64, error)
	GetConsignmentByID(id uint) (*models.Consignment, error)
	GetConsignmentBySlug(slug string) (*models.Consignment, error)
	GetAllConsignments(page, limit int, status string) ([]models.Consignment, int64, error)
	UpdateConsignmentStatus(id uint, status string) error

	// Locations
	GetLocations() ([]models.Location, error)

	// Users
	GetUsers(page, limit int) ([]models.User, int64, error)
	GetUserByID(id uint) (*models.User, error)

	// Transactions
	GetTransactions(page, limit int) ([]models.Transaction, int64, error)

	// Dashboard stats
	GetDashboardStats() (map[string]interface{}, error)
}

// MySQLRepository implements Repository
type MySQLRepository struct {
	db *gorm.DB
}

// NewMySQLRepository creates new repository
func NewMySQLRepository(db *gorm.DB) *MySQLRepository {
	return &MySQLRepository{db: db}
}

// GetApprovedConsignments returns approved consignments with pagination
// When lat/lng are provided (non-zero), sorts by distance using Haversine formula
// When maxDistance > 0, filters to only show properties within that distance (km)
func (r *MySQLRepository) GetApprovedConsignments(page, limit int, search, province, phone string, lat, lng, maxDistance float64, filters map[string]string) ([]models.Consignment, int64, error) {
	var consignments []models.Consignment
	var total int64

	query := r.db.Model(&models.Consignment{}).Where("status = ?", "approved")

	if search != "" {
		query = query.Where("title LIKE ? OR address LIKE ? OR code LIKE ? OR keywords LIKE ? OR consigner_phone LIKE ? OR seller_phone LIKE ? OR CAST(order_number AS CHAR) LIKE ?",
			"%"+search+"%", "%"+search+"%", "%"+search+"%", "%"+search+"%", "%"+search+"%", "%"+search+"%", "%"+search+"%")
	}
	if province != "" {
		query = query.Where("province = ?", province)
	}
	if phone != "" {
		query = query.Where("CAST(order_number AS CHAR) LIKE ? OR seller_phone LIKE ? OR consigner_phone LIKE ?", "%"+phone+"%", "%"+phone+"%", "%"+phone+"%")
	}

	// Advanced filters
	if filters != nil {
		if v := filters["district"]; v != "" {
			query = query.Where("ward = ?", v)
		}
		if v := filters["property_type"]; v != "" {
			query = query.Where("JSON_CONTAINS(land_types, ?)", `"`+v+`"`)
		}
		if v := filters["house_on_land"]; v != "" {
			query = query.Where("has_house = ?", v)
		}
		if v := filters["tho_cu"]; v != "" {
			query = query.Where("residential_type = ?", v)
		}
		if v := filters["road_type"]; v != "" {
			query = query.Where("road_display = ?", v)
		}
		if v := filters["direction"]; v != "" {
			query = query.Where("JSON_CONTAINS(land_directions, ?)", `"`+v+`"`)
		}
		if v := filters["so_to"]; v != "" {
			query = query.Where("sheet_number LIKE ?", "%"+v+"%")
		}
		if v := filters["so_thua"]; v != "" {
			query = query.Where("parcel_number LIKE ?", "%"+v+"%")
		}
		if v := filters["category"]; v != "" {
			query = query.Where("category = ?", v)
		}
		// Price range filter (values in millions)
		if v := filters["price_range"]; v != "" {
			applyRangeFilter(query, v, "price", 1000000, &query)
		}
		// Area range filter
		if v := filters["area_range"]; v != "" {
			query = query.Where("area_range = ?", v)
		}
		// Floor area range filter
		if v := filters["floor_area_range"]; v != "" {
			applyRangeFilter(query, v, "floor_area", 1, &query)
		}
		// Frontage range filter
		if v := filters["frontage"]; v != "" {
			query = query.Where("frontage_range = ?", v)
		}
	}

	offset := (page - 1) * limit

	// Determine sort order
	sortOrder := "created_at DESC"
	if filters != nil {
		switch filters["sort"] {
		case "newest":
			sortOrder = "created_at DESC"
		case "oldest":
			sortOrder = "created_at ASC"
		case "price_asc":
			sortOrder = "price ASC"
		case "price_desc":
			sortOrder = "price DESC"
		case "area_asc":
			sortOrder = "CAST(residential_area AS DECIMAL(10,2)) ASC"
		case "area_desc":
			sortOrder = "CAST(residential_area AS DECIMAL(10,2)) DESC"
		}
	}

	// If user location is provided, sort by distance (Haversine formula)
	if lat != 0 && lng != 0 {
		distanceExpr := fmt.Sprintf(
			"(6371 * acos(LEAST(1.0, cos(radians(%f)) * cos(radians(CAST(latitude AS DECIMAL(10,7)))) * cos(radians(CAST(longitude AS DECIMAL(10,7))) - radians(%f)) + sin(radians(%f)) * sin(radians(CAST(latitude AS DECIMAL(10,7)))))))",
			lat, lng, lat,
		)

		geoQuery := query.
			Select(fmt.Sprintf("*, %s AS distance", distanceExpr)).
			Where("latitude IS NOT NULL AND latitude != '' AND longitude IS NOT NULL AND longitude != ''")

		// Filter by max distance if specified
		if maxDistance > 0 {
			geoQuery = geoQuery.Where(fmt.Sprintf("%s <= %f", distanceExpr, maxDistance))
		}

		// Count matching records for pagination
		geoQuery.Count(&total)

		err := geoQuery.
			Preload("User").
			Offset(offset).Limit(limit).
			Order("distance ASC").
			Find(&consignments).Error

		return consignments, total, err
	}

	query.Count(&total)

	err := query.Preload("User").
		Offset(offset).Limit(limit).
		Order(sortOrder).
		Find(&consignments).Error

	return consignments, total, err
}

// applyRangeFilter applies a range filter like "0-500", "500-1000", "5000+"
func applyRangeFilter(_ *gorm.DB, rangeStr, column string, multiplier float64, result **gorm.DB) {
	if strings.HasSuffix(rangeStr, "+") {
		minStr := strings.TrimSuffix(rangeStr, "+")
		if min, err := strconv.ParseFloat(minStr, 64); err == nil {
			*result = (*result).Where(fmt.Sprintf("%s >= ?", column), min*multiplier)
		}
	} else if parts := strings.SplitN(rangeStr, "-", 2); len(parts) == 2 {
		min, err1 := strconv.ParseFloat(parts[0], 64)
		max, err2 := strconv.ParseFloat(parts[1], 64)
		if err1 == nil && err2 == nil {
			*result = (*result).Where(fmt.Sprintf("%s >= ? AND %s <= ?", column, column), min*multiplier, max*multiplier)
		}
	}
}

// GetConsignmentByID returns single consignment
func (r *MySQLRepository) GetConsignmentByID(id uint) (*models.Consignment, error) {
	var consignment models.Consignment
	err := r.db.Preload("User").First(&consignment, id).Error
	if err != nil {
		return nil, err
	}
	return &consignment, nil
}

// GetConsignmentBySlug returns single consignment by seo_url
func (r *MySQLRepository) GetConsignmentBySlug(slug string) (*models.Consignment, error) {
	var consignment models.Consignment
	err := r.db.Preload("User").Where("seo_url = ? AND status = 'approved'", slug).First(&consignment).Error
	if err != nil {
		return nil, err
	}
	return &consignment, nil
}

// GetAllConsignments returns all consignments for admin
func (r *MySQLRepository) GetAllConsignments(page, limit int, status string) ([]models.Consignment, int64, error) {
	var consignments []models.Consignment
	var total int64

	query := r.db.Model(&models.Consignment{})
	if status != "" {
		query = query.Where("status = ?", status)
	}

	query.Count(&total)

	offset := (page - 1) * limit
	err := query.Preload("User").
		Offset(offset).Limit(limit).
		Order("created_at DESC").
		Find(&consignments).Error

	return consignments, total, err
}

// UpdateConsignmentStatus updates status
func (r *MySQLRepository) UpdateConsignmentStatus(id uint, status string) error {
	return r.db.Model(&models.Consignment{}).Where("id = ?", id).Update("status", status).Error
}

// GetLocations returns provinces with count
func (r *MySQLRepository) GetLocations() ([]models.Location, error) {
	var locations []models.Location
	err := r.db.Model(&models.Consignment{}).
		Select("province, COUNT(*) as count").
		Where("status = ?", "approved").
		Group("province").
		Find(&locations).Error
	return locations, err
}

// GetUsers returns users with pagination (excludes hidden users)
func (r *MySQLRepository) GetUsers(page, limit int) ([]models.User, int64, error) {
	var users []models.User
	var total int64

	r.db.Model(&models.User{}).Where("is_hidden = ?", false).Count(&total)

	offset := (page - 1) * limit
	err := r.db.Where("is_hidden = ?", false).Offset(offset).Limit(limit).Order("created_at DESC").Find(&users).Error

	return users, total, err
}

// GetUserByID returns single user
func (r *MySQLRepository) GetUserByID(id uint) (*models.User, error) {
	var user models.User
	err := r.db.First(&user, id).Error
	if err != nil {
		return nil, err
	}
	return &user, nil
}

// GetTransactions returns transactions with pagination
func (r *MySQLRepository) GetTransactions(page, limit int) ([]models.Transaction, int64, error) {
	var transactions []models.Transaction
	var total int64

	r.db.Model(&models.Transaction{}).Count(&total)

	offset := (page - 1) * limit
	err := r.db.Preload("User").Offset(offset).Limit(limit).Order("created_at DESC").Find(&transactions).Error

	return transactions, total, err
}

// GetDashboardStats returns dashboard statistics
func (r *MySQLRepository) GetDashboardStats() (map[string]interface{}, error) {
	var totalUsers int64
	var totalConsignments int64
	var pendingConsignments int64
	var totalTransactions float64

	r.db.Model(&models.User{}).Count(&totalUsers)
	r.db.Model(&models.Consignment{}).Count(&totalConsignments)
	r.db.Model(&models.Consignment{}).Where("status = ?", "pending").Count(&pendingConsignments)
	r.db.Model(&models.Transaction{}).Where("status = ?", "completed").Select("COALESCE(SUM(amount), 0)").Scan(&totalTransactions)

	return map[string]interface{}{
		"total_users":          totalUsers,
		"total_consignments":   totalConsignments,
		"pending_consignments": pendingConsignments,
		"total_transactions":   totalTransactions,
	}, nil
}

// ---- Report Methods ----

// MonthlyCount holds count per month
type MonthlyCount struct {
	Year  int   `json:"year"`
	Month int   `json:"month"`
	Count int64 `json:"count"`
}

// MonthlyRevenue holds revenue per month
type MonthlyRevenue struct {
	Year   int     `json:"year"`
	Month  int     `json:"month"`
	Amount float64 `json:"amount"`
}

// StatusCount holds count per status
type StatusCount struct {
	Status string `json:"status"`
	Count  int64  `json:"count"`
}

// ProvinceCount holds count per province
type ProvinceCount struct {
	Province string `json:"province"`
	Count    int64  `json:"count"`
}

// GetConsignmentsByMonth returns consignment counts grouped by month
func (r *MySQLRepository) GetConsignmentsByMonth(months int) ([]MonthlyCount, error) {
	var results []MonthlyCount
	err := r.db.Model(&models.Consignment{}).
		Select("YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count").
		Where("created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)", months).
		Group("YEAR(created_at), MONTH(created_at)").
		Order("year ASC, month ASC").
		Find(&results).Error
	return results, err
}

// GetConsignmentsByStatus returns consignment counts grouped by status
func (r *MySQLRepository) GetConsignmentsByStatus() ([]StatusCount, error) {
	var results []StatusCount
	err := r.db.Model(&models.Consignment{}).
		Select("status, COUNT(*) as count").
		Group("status").
		Find(&results).Error
	return results, err
}

// GetConsignmentsByProvince returns top provinces by consignment count
func (r *MySQLRepository) GetConsignmentsByProvince(limit int) ([]ProvinceCount, error) {
	var results []ProvinceCount
	err := r.db.Model(&models.Consignment{}).
		Select("province, COUNT(*) as count").
		Where("province != '' AND province IS NOT NULL").
		Group("province").
		Order("count DESC").
		Limit(limit).
		Find(&results).Error
	return results, err
}

// GetRevenueByMonth returns revenue grouped by month
func (r *MySQLRepository) GetRevenueByMonth(months int) ([]MonthlyRevenue, error) {
	var results []MonthlyRevenue
	err := r.db.Model(&models.Transaction{}).
		Select("YEAR(created_at) as year, MONTH(created_at) as month, COALESCE(SUM(amount), 0) as amount").
		Where("status = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)", "completed", months).
		Group("YEAR(created_at), MONTH(created_at)").
		Order("year ASC, month ASC").
		Find(&results).Error
	return results, err
}

// GetAllConsignmentsForExport returns all consignments without pagination
func (r *MySQLRepository) GetAllConsignmentsForExport() ([]models.Consignment, error) {
	var consignments []models.Consignment
	err := r.db.Preload("User").
		Order("created_at DESC").
		Find(&consignments).Error
	return consignments, err
}

// GetAllTransactionsForExport returns all transactions without pagination
func (r *MySQLRepository) GetAllTransactionsForExport() ([]models.Transaction, error) {
	var transactions []models.Transaction
	err := r.db.Preload("User").
		Order("created_at DESC").
		Find(&transactions).Error
	return transactions, err
}
