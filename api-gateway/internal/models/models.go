package models

import (
	"time"
)

// User represents user model
type User struct {
	ID        uint      `json:"id" gorm:"primaryKey"`
	Name      string    `json:"name"`
	Email     string    `json:"email" gorm:"uniqueIndex"`
	Phone     string    `json:"phone"`
	Balance   float64   `json:"balance" gorm:"default:0"`
	Status    string    `json:"status" gorm:"default:active"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// Consignment represents consignment/property model
type Consignment struct {
	ID          uint      `json:"id" gorm:"primaryKey"`
	UserID      uint      `json:"user_id"`
	Code        string    `json:"code" gorm:"uniqueIndex"`
	Title       string    `json:"title"`
	Description string    `json:"description" gorm:"type:text"`
	Price       float64   `json:"price"`
	Area        float64   `json:"area"`
	Address     string    `json:"address"`
	Province    string    `json:"province"`
	District    string    `json:"district"`
	Ward        string    `json:"ward"`
	CategoryID  uint      `json:"category_id"`
	Status      string    `json:"status" gorm:"default:pending"` // pending, approved, rejected, sold
	Images      string    `json:"images" gorm:"type:json"`
	CreatedAt   time.Time `json:"created_at"`
	UpdatedAt   time.Time `json:"updated_at"`

	User     User     `json:"user,omitempty" gorm:"foreignKey:UserID"`
	Category Category `json:"category,omitempty" gorm:"foreignKey:CategoryID"`
}

// Category represents property category
type Category struct {
	ID        uint      `json:"id" gorm:"primaryKey"`
	Name      string    `json:"name"`
	Slug      string    `json:"slug" gorm:"uniqueIndex"`
	CreatedAt time.Time `json:"created_at"`
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

// Location for province/district data
type Location struct {
	Province string `json:"province"`
	District string `json:"district"`
	Count    int    `json:"count"`
}
