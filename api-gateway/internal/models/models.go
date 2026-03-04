package models

import (
	"encoding/json"
	"time"

	"gorm.io/gorm"
)

// User represents user model
type User struct {
	ID        uint      `json:"id" gorm:"primaryKey"`
	Name      string    `json:"name"`
	Email     string    `json:"email" gorm:"uniqueIndex"`
	Phone     string    `json:"phone"`
	Balance   float64   `json:"balance" gorm:"default:0"`
	Status    string    `json:"status" gorm:"default:active"`
	IsHidden  bool      `json:"-" gorm:"default:false"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// Consignment represents consignment/property model
type Consignment struct {
	ID              uint            `json:"id" gorm:"primaryKey"`
	UserID          uint            `json:"user_id"`
	Code            string          `json:"code" gorm:"uniqueIndex"`
	Title           string          `json:"title"`
	Description     string          `json:"description" gorm:"type:text"`
	FeaturedImage   string          `json:"featured_image"`
	Price           float64         `json:"price"`
	MinPrice        float64         `json:"min_price"`
	Address         string          `json:"address"`
	Province        string          `json:"province"`
	Ward            string          `json:"ward"`
	AreaDimensions  string          `json:"area_dimensions"`
	AreaRange       string          `json:"area_range"`
	ResidentialArea string          `json:"residential_area"`
	ResidentialType string          `json:"residential_type"`
	LandDirections  json.RawMessage `json:"land_directions" gorm:"type:json"`
	LandTypes       json.RawMessage `json:"land_types" gorm:"type:json"`
	Road            string          `json:"road"`
	RoadDisplay     string          `json:"road_display"`
	HasHouse        string          `json:"has_house"`
	FrontageActual  string          `json:"frontage_actual"`
	FrontageRange   string          `json:"frontage_range"`
	FloorArea       float64         `json:"floor_area"`
	Keywords        string          `json:"keywords"`
	Latitude        string          `json:"latitude"`
	Longitude       string          `json:"longitude"`
	SeoUrl          string          `json:"seo_url"`
	Status          string          `json:"status" gorm:"default:pending"` // pending, approved, rejected, sold
	OrderNumber     int             `json:"order_number"`
	DisplayOrder    int             `json:"display_order"`
	Images          json.RawMessage `json:"images" gorm:"type:json"`
	SheetNumber     string          `json:"sheet_number"`
	ParcelNumber    string          `json:"parcel_number"`
	ConsignerName   string          `json:"consigner_name"`
	ConsignerPhone  string          `json:"consigner_phone"`
	SellerPhone     string          `json:"seller_phone"`
	CreatedAt       time.Time       `json:"created_at"`
	UpdatedAt       time.Time       `json:"updated_at"`
	DeletedAt       gorm.DeletedAt  `json:"-" gorm:"index"`

	// Computed field (not stored in DB, read-only from SELECT)
	Distance float64 `json:"distance,omitempty" gorm:"->"`

	User User `json:"user,omitempty" gorm:"foreignKey:UserID"`
}

// Transaction represents payment transaction
type Transaction struct {
	ID            uint      `json:"id" gorm:"primaryKey"`
	UserID        uint      `json:"user_id"`
	Type          string    `json:"type"` // deposit, purchase
	Amount        float64   `json:"amount"`
	PaymentMethod string    `json:"payment_method"`
	Status        string    `json:"status"` // pending, completed, failed
	Description   string    `json:"description"`
	CreatedAt     time.Time `json:"created_at"`

	User User `json:"user,omitempty" gorm:"foreignKey:UserID"`
}

// Location for province data
type Location struct {
	Province string `json:"province"`
	Count    int    `json:"count"`
}
