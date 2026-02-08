package handlers

import (
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"

	"khodat/api-gateway/internal/repository"
	"khodat/api-gateway/pkg/response"
)

// AdminHandler handles admin requests
type AdminHandler struct {
	repo *repository.MySQLRepository
}

// NewAdminHandler creates new handler
func NewAdminHandler(repo *repository.MySQLRepository) *AdminHandler {
	return &AdminHandler{repo: repo}
}

// Dashboard returns admin dashboard stats
func (h *AdminHandler) Dashboard(c *gin.Context) {
	stats, err := h.repo.GetDashboardStats()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch dashboard stats")
		return
	}

	response.Success(c, stats)
}

// ListUsers returns all users
func (h *AdminHandler) ListUsers(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "20"))

	users, total, err := h.repo.GetUsers(page, limit)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch users")
		return
	}

	response.Paginated(c, users, total, page, limit)
}

// ListConsignments returns all consignments for admin
func (h *AdminHandler) ListConsignments(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "20"))
	status := c.Query("status")

	consignments, total, err := h.repo.GetAllConsignments(page, limit, status)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch consignments")
		return
	}

	response.Paginated(c, consignments, total, page, limit)
}

// ApproveConsignment approves a consignment
func (h *AdminHandler) ApproveConsignment(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid ID")
		return
	}

	if err := h.repo.UpdateConsignmentStatus(uint(id), "approved"); err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to approve consignment")
		return
	}

	response.Success(c, gin.H{"message": "Consignment approved successfully"})
}

// RejectConsignment rejects a consignment
func (h *AdminHandler) RejectConsignment(c *gin.Context) {
	id, err := strconv.ParseUint(c.Param("id"), 10, 32)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid ID")
		return
	}

	if err := h.repo.UpdateConsignmentStatus(uint(id), "rejected"); err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to reject consignment")
		return
	}

	response.Success(c, gin.H{"message": "Consignment rejected successfully"})
}

// ListTransactions returns all transactions
func (h *AdminHandler) ListTransactions(c *gin.Context) {
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "20"))

	transactions, total, err := h.repo.GetTransactions(page, limit)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch transactions")
		return
	}

	response.Paginated(c, transactions, total, page, limit)
}
