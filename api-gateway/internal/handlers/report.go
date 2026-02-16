package handlers

import (
	"fmt"
	"net/http"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/xuri/excelize/v2"

	"khodat/api-gateway/internal/repository"
	"khodat/api-gateway/pkg/response"
)

// ReportHandler handles report requests
type ReportHandler struct {
	repo *repository.MySQLRepository
}

// NewReportHandler creates new handler
func NewReportHandler(repo *repository.MySQLRepository) *ReportHandler {
	return &ReportHandler{repo: repo}
}

// Overview returns aggregated data for charts
func (h *ReportHandler) Overview(c *gin.Context) {
	// Get all report data in parallel-style (sequential in Go, but grouped)
	consignmentsByMonth, err := h.repo.GetConsignmentsByMonth(12)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch consignments by month")
		return
	}

	consignmentsByStatus, err := h.repo.GetConsignmentsByStatus()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch consignments by status")
		return
	}

	consignmentsByProvince, err := h.repo.GetConsignmentsByProvince(10)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch consignments by province")
		return
	}

	revenueByMonth, err := h.repo.GetRevenueByMonth(12)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch revenue by month")
		return
	}

	stats, err := h.repo.GetDashboardStats()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to fetch stats")
		return
	}

	response.Success(c, gin.H{
		"consignments_by_month":    consignmentsByMonth,
		"consignments_by_status":   consignmentsByStatus,
		"consignments_by_province": consignmentsByProvince,
		"revenue_by_month":         revenueByMonth,
		"stats":                    stats,
	})
}

// ExportExcel exports data as Excel file
func (h *ReportHandler) ExportExcel(c *gin.Context) {
	f := excelize.NewFile()
	defer f.Close()

	// ---- Sheet 1: Ký gửi ----
	sheetName := "Ký gửi"
	f.SetSheetName("Sheet1", sheetName)

	// Headers
	headers := []string{"ID", "Mã", "Tiêu đề", "Giá (VNĐ)", "Địa chỉ", "Tỉnh/TP", "Phường/Xã", "Trạng thái", "Ngày tạo", "Người gửi"}
	for i, h := range headers {
		cell := fmt.Sprintf("%c1", 'A'+i)
		f.SetCellValue(sheetName, cell, h)
	}

	// Style headers
	headerStyle, _ := f.NewStyle(&excelize.Style{
		Font:      &excelize.Font{Bold: true, Color: "FFFFFF", Size: 11},
		Fill:      excelize.Fill{Type: "pattern", Color: []string{"4472C4"}, Pattern: 1},
		Alignment: &excelize.Alignment{Horizontal: "center"},
		Border: []excelize.Border{
			{Type: "bottom", Color: "000000", Style: 1},
		},
	})
	f.SetRowStyle(sheetName, 1, 1, headerStyle)

	// Data
	consignments, err := h.repo.GetAllConsignmentsForExport()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to export consignments")
		return
	}

	statusMap := map[string]string{
		"pending":  "Chờ duyệt",
		"approved": "Đã duyệt",
		"rejected": "Từ chối",
		"sold":     "Đã bán",
	}

	for i, item := range consignments {
		row := i + 2
		f.SetCellValue(sheetName, fmt.Sprintf("A%d", row), item.ID)
		f.SetCellValue(sheetName, fmt.Sprintf("B%d", row), item.Code)
		f.SetCellValue(sheetName, fmt.Sprintf("C%d", row), item.Title)
		f.SetCellValue(sheetName, fmt.Sprintf("D%d", row), item.Price)
		f.SetCellValue(sheetName, fmt.Sprintf("E%d", row), item.Address)
		f.SetCellValue(sheetName, fmt.Sprintf("F%d", row), item.Province)
		f.SetCellValue(sheetName, fmt.Sprintf("G%d", row), item.Ward)
		status := item.Status
		if v, ok := statusMap[item.Status]; ok {
			status = v
		}
		f.SetCellValue(sheetName, fmt.Sprintf("H%d", row), status)
		f.SetCellValue(sheetName, fmt.Sprintf("I%d", row), item.CreatedAt.Format("02/01/2006"))
		f.SetCellValue(sheetName, fmt.Sprintf("J%d", row), item.User.Name)
	}

	// Auto-fit columns
	for i := 0; i < len(headers); i++ {
		col := fmt.Sprintf("%c", 'A'+i)
		f.SetColWidth(sheetName, col, col, 18)
	}

	// ---- Sheet 2: Giao dịch ----
	txSheet := "Giao dịch"
	f.NewSheet(txSheet)

	txHeaders := []string{"ID", "Loại", "Số tiền (VNĐ)", "Phương thức", "Trạng thái", "Mô tả", "Ngày", "Người dùng"}
	for i, h := range txHeaders {
		cell := fmt.Sprintf("%c1", 'A'+i)
		f.SetCellValue(txSheet, cell, h)
	}
	f.SetRowStyle(txSheet, 1, 1, headerStyle)

	transactions, err := h.repo.GetAllTransactionsForExport()
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to export transactions")
		return
	}

	txTypeMap := map[string]string{
		"deposit":  "Nạp tiền",
		"purchase": "Mua",
	}
	txStatusMap := map[string]string{
		"pending":   "Chờ xử lý",
		"completed": "Hoàn thành",
		"failed":    "Thất bại",
	}

	for i, item := range transactions {
		row := i + 2
		f.SetCellValue(txSheet, fmt.Sprintf("A%d", row), item.ID)
		txType := item.Type
		if v, ok := txTypeMap[item.Type]; ok {
			txType = v
		}
		f.SetCellValue(txSheet, fmt.Sprintf("B%d", row), txType)
		f.SetCellValue(txSheet, fmt.Sprintf("C%d", row), item.Amount)
		f.SetCellValue(txSheet, fmt.Sprintf("D%d", row), item.PaymentMethod)
		txStatus := item.Status
		if v, ok := txStatusMap[item.Status]; ok {
			txStatus = v
		}
		f.SetCellValue(txSheet, fmt.Sprintf("E%d", row), txStatus)
		f.SetCellValue(txSheet, fmt.Sprintf("F%d", row), item.Description)
		f.SetCellValue(txSheet, fmt.Sprintf("G%d", row), item.CreatedAt.Format("02/01/2006"))
		f.SetCellValue(txSheet, fmt.Sprintf("H%d", row), item.User.Name)
	}

	for i := 0; i < len(txHeaders); i++ {
		col := fmt.Sprintf("%c", 'A'+i)
		f.SetColWidth(txSheet, col, col, 18)
	}

	// ---- Sheet 3: Thống kê ----
	statsSheet := "Thống kê"
	f.NewSheet(statsSheet)

	stats, _ := h.repo.GetDashboardStats()
	summaryData := [][]interface{}{
		{"Chỉ số", "Giá trị"},
		{"Tổng số ký gửi", stats["total_consignments"]},
		{"Ký gửi chờ duyệt", stats["pending_consignments"]},
		{"Tổng người dùng", stats["total_users"]},
		{"Tổng doanh thu (VNĐ)", stats["total_transactions"]},
		{"Ngày xuất báo cáo", time.Now().Format("02/01/2006 15:04")},
	}

	for i, row := range summaryData {
		f.SetCellValue(statsSheet, fmt.Sprintf("A%d", i+1), row[0])
		f.SetCellValue(statsSheet, fmt.Sprintf("B%d", i+1), row[1])
	}
	f.SetRowStyle(statsSheet, 1, 1, headerStyle)
	f.SetColWidth(statsSheet, "A", "A", 25)
	f.SetColWidth(statsSheet, "B", "B", 25)

	// Write response
	filename := fmt.Sprintf("bao-cao-khodat_%s.xlsx", time.Now().Format("2006-01-02"))
	c.Header("Content-Type", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
	c.Header("Content-Disposition", fmt.Sprintf("attachment; filename=%s", filename))
	c.Header("Content-Transfer-Encoding", "binary")

	if err := f.Write(c.Writer); err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to generate Excel file")
		return
	}
}
