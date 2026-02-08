package repository

import (
	"khodat/api-gateway/internal/models"

	"gorm.io/gorm"
)

// Repository interface for database operations
type Repository interface {
	// Consignments
	GetApprovedConsignments(page, limit int, search, province string) ([]models.Consignment, int64, error)
	GetConsignmentByID(id uint) (*models.Consignment, error)
	GetAllConsignments(page, limit int, status string) ([]models.Consignment, int64, error)
	UpdateConsignmentStatus(id uint, status string) error

	// Categories
	GetCategories() ([]models.Category, error)

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
func (r *MySQLRepository) GetApprovedConsignments(page, limit int, search, province string) ([]models.Consignment, int64, error) {
	var consignments []models.Consignment
	var total int64

	query := r.db.Model(&models.Consignment{}).Where("status = ?", "approved")

	if search != "" {
		query = query.Where("title LIKE ? OR address LIKE ?", "%"+search+"%", "%"+search+"%")
	}
	if province != "" {
		query = query.Where("province = ?", province)
	}

	query.Count(&total)

	offset := (page - 1) * limit
	err := query.Preload("User").Preload("Category").
		Offset(offset).Limit(limit).
		Order("created_at DESC").
		Find(&consignments).Error

	return consignments, total, err
}

// GetConsignmentByID returns single consignment
func (r *MySQLRepository) GetConsignmentByID(id uint) (*models.Consignment, error) {
	var consignment models.Consignment
	err := r.db.Preload("User").Preload("Category").First(&consignment, id).Error
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
	err := query.Preload("User").Preload("Category").
		Offset(offset).Limit(limit).
		Order("created_at DESC").
		Find(&consignments).Error

	return consignments, total, err
}

// UpdateConsignmentStatus updates status
func (r *MySQLRepository) UpdateConsignmentStatus(id uint, status string) error {
	return r.db.Model(&models.Consignment{}).Where("id = ?", id).Update("status", status).Error
}

// GetCategories returns all categories
func (r *MySQLRepository) GetCategories() ([]models.Category, error) {
	var categories []models.Category
	err := r.db.Find(&categories).Error
	return categories, err
}

// GetLocations returns provinces with count
func (r *MySQLRepository) GetLocations() ([]models.Location, error) {
	var locations []models.Location
	err := r.db.Model(&models.Consignment{}).
		Select("province, district, COUNT(*) as count").
		Where("status = ?", "approved").
		Group("province, district").
		Find(&locations).Error
	return locations, err
}

// GetUsers returns users with pagination
func (r *MySQLRepository) GetUsers(page, limit int) ([]models.User, int64, error) {
	var users []models.User
	var total int64

	r.db.Model(&models.User{}).Count(&total)

	offset := (page - 1) * limit
	err := r.db.Offset(offset).Limit(limit).Order("created_at DESC").Find(&users).Error

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
