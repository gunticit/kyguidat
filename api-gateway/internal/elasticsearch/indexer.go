package elasticsearch

import (
	"bytes"
	"encoding/json"
	"fmt"
	"log"
	"strings"

	"khodat/api-gateway/internal/models"
)

const IndexName = "consignments"

// indexMapping defines the Elasticsearch mapping with Vietnamese support
var indexMapping = `{
	"settings": {
		"number_of_shards": 1,
		"number_of_replicas": 0,
		"analysis": {
			"analyzer": {
				"vietnamese_analyzer": {
					"type": "custom",
					"tokenizer": "icu_tokenizer",
					"filter": ["icu_folding", "lowercase"]
				},
				"vietnamese_search": {
					"type": "custom",
					"tokenizer": "icu_tokenizer",
					"filter": ["icu_folding", "lowercase"]
				}
			}
		}
	},
	"mappings": {
		"properties": {
			"id":               { "type": "integer" },
			"user_id":          { "type": "integer" },
			"code":             { "type": "keyword" },
			"order_number":     { "type": "integer" },
			"title":            { "type": "text", "analyzer": "vietnamese_analyzer", "search_analyzer": "vietnamese_search", "fields": { "keyword": { "type": "keyword" } } },
			"description":      { "type": "text", "analyzer": "vietnamese_analyzer", "search_analyzer": "vietnamese_search" },
			"address":          { "type": "text", "analyzer": "vietnamese_analyzer", "search_analyzer": "vietnamese_search", "fields": { "keyword": { "type": "keyword" } } },
			"keywords":         { "type": "text", "analyzer": "vietnamese_analyzer", "search_analyzer": "vietnamese_search" },
			"featured_image":   { "type": "keyword", "index": false },
			"images":           { "type": "object", "enabled": false },
			"price":            { "type": "double" },
			"min_price":        { "type": "double" },
			"province":         { "type": "keyword" },
			"ward":             { "type": "keyword" },
			"road":             { "type": "keyword" },
			"road_display":     { "type": "keyword" },
			"has_house":        { "type": "keyword" },
			"residential_type": { "type": "keyword" },
			"residential_area": { "type": "double" },
			"area_dimensions":  { "type": "keyword" },
			"area_range":       { "type": "keyword" },
			"floor_area":       { "type": "double" },
			"frontage_actual":  { "type": "double" },
			"frontage_range":   { "type": "keyword" },
			"land_directions":  { "type": "keyword" },
			"land_types":       { "type": "keyword" },
			"sheet_number":     { "type": "keyword" },
			"parcel_number":    { "type": "keyword" },
			"latitude":         { "type": "keyword" },
			"longitude":        { "type": "keyword" },
			"seo_url":          { "type": "keyword" },
			"status":           { "type": "keyword" },
			"display_order":    { "type": "integer" },
			"consigner_phone":  { "type": "keyword" },
			"seller_phone":     { "type": "keyword" },
			"created_at":       { "type": "date", "format": "yyyy-MM-dd'T'HH:mm:ssZ||yyyy-MM-dd HH:mm:ss||epoch_millis" },
			"updated_at":       { "type": "date", "format": "yyyy-MM-dd'T'HH:mm:ssZ||yyyy-MM-dd HH:mm:ss||epoch_millis" },
			"approved_at":      { "type": "date", "format": "yyyy-MM-dd'T'HH:mm:ssZ||yyyy-MM-dd HH:mm:ss||epoch_millis" },
			"user_name":        { "type": "keyword" }
		}
	}
}`

// ConsignmentDoc represents an ES document for a consignment
type ConsignmentDoc struct {
	ID              uint        `json:"id"`
	UserID          uint        `json:"user_id"`
	Code            string      `json:"code"`
	OrderNumber     int         `json:"order_number"`
	Title           string      `json:"title"`
	Description     string      `json:"description"`
	Address         string      `json:"address"`
	Keywords        string      `json:"keywords"`
	FeaturedImage   string      `json:"featured_image"`
	Images          interface{} `json:"images"`
	Price           float64     `json:"price"`
	MinPrice        float64     `json:"min_price"`
	Province        string      `json:"province"`
	Ward            string      `json:"ward"`
	Road            string      `json:"road"`
	RoadDisplay     string      `json:"road_display"`
	HasHouse        string      `json:"has_house"`
	ResidentialType string      `json:"residential_type"`
	ResidentialArea string      `json:"residential_area"`
	AreaDimensions  string      `json:"area_dimensions"`
	AreaRange       string      `json:"area_range"`
	FloorArea       float64     `json:"floor_area"`
	FrontageActual  string      `json:"frontage_actual"`
	FrontageRange   string      `json:"frontage_range"`
	LandDirections  interface{} `json:"land_directions"`
	LandTypes       interface{} `json:"land_types"`
	SheetNumber     string      `json:"sheet_number"`
	ParcelNumber    string      `json:"parcel_number"`
	Latitude        string      `json:"latitude"`
	Longitude       string      `json:"longitude"`
	SeoUrl          string      `json:"seo_url"`
	Status          string      `json:"status"`
	DisplayOrder    int         `json:"display_order"`
	ConsignerPhone  string      `json:"consigner_phone"`
	SellerPhone     string      `json:"seller_phone"`
	CreatedAt       string      `json:"created_at"`
	UpdatedAt       string      `json:"updated_at"`
	ApprovedAt      *string     `json:"approved_at,omitempty"`
	UserName        string      `json:"user_name"`
}

// EnsureIndex creates the consignments index if it doesn't exist
func (c *Client) EnsureIndex() error {
	// Check if index exists
	_, statusCode, err := c.doRequest("HEAD", "/"+IndexName, nil)
	if err != nil {
		return fmt.Errorf("error checking index: %w", err)
	}

	if statusCode == 200 {
		log.Printf("✅ Index '%s' already exists", IndexName)
		return nil
	}

	// Create index with mapping
	_, statusCode, err = c.doRequest("PUT", "/"+IndexName, strings.NewReader(indexMapping))
	if err != nil {
		return fmt.Errorf("error creating index: %w", err)
	}

	if statusCode >= 400 {
		return fmt.Errorf("error creating index, status: %d", statusCode)
	}

	log.Printf("✅ Created index '%s' with Vietnamese analyzer", IndexName)
	return nil
}

// IndexConsignment indexes a single consignment document
func (c *Client) IndexConsignment(consignment models.Consignment) error {
	doc := consignmentToDoc(consignment)

	data, err := json.Marshal(doc)
	if err != nil {
		return fmt.Errorf("error marshaling document: %w", err)
	}

	path := fmt.Sprintf("/%s/_doc/%d", IndexName, doc.ID)
	_, statusCode, err := c.doRequest("PUT", path, bytes.NewReader(data))
	if err != nil {
		return fmt.Errorf("error indexing document: %w", err)
	}

	if statusCode >= 400 {
		return fmt.Errorf("error indexing document %d, status: %d", doc.ID, statusCode)
	}

	return nil
}

// BulkIndex indexes multiple consignments using the bulk API
func (c *Client) BulkIndex(consignments []models.Consignment) error {
	if len(consignments) == 0 {
		return nil
	}

	var buf bytes.Buffer

	for _, consignment := range consignments {
		doc := consignmentToDoc(consignment)

		// Action line
		meta := map[string]interface{}{
			"index": map[string]interface{}{
				"_index": IndexName,
				"_id":    fmt.Sprintf("%d", doc.ID),
			},
		}
		metaBytes, _ := json.Marshal(meta)
		buf.Write(metaBytes)
		buf.WriteByte('\n')

		// Document line
		docBytes, _ := json.Marshal(doc)
		buf.Write(docBytes)
		buf.WriteByte('\n')
	}

	respBody, statusCode, err := c.doRequestWithContentType("POST", "/_bulk?refresh=true", &buf, "application/x-ndjson")
	if err != nil {
		return fmt.Errorf("bulk index error: %w", err)
	}

	if statusCode >= 400 {
		return fmt.Errorf("bulk index error (status %d): %s", statusCode, string(respBody))
	}

	log.Printf("✅ Bulk indexed %d consignments", len(consignments))
	return nil
}

// DeleteConsignment removes a consignment from the index
func (c *Client) DeleteConsignment(id uint) error {
	path := fmt.Sprintf("/%s/_doc/%d", IndexName, id)
	_, _, err := c.doRequest("DELETE", path, nil)
	return err
}

// consignmentToDoc converts a Consignment model to an ES document
func consignmentToDoc(c models.Consignment) ConsignmentDoc {
	doc := ConsignmentDoc{
		ID:              c.ID,
		UserID:          c.UserID,
		Code:            c.Code,
		OrderNumber:     c.OrderNumber,
		Title:           c.Title,
		Description:     c.Description,
		Address:         c.Address,
		Keywords:        c.Keywords,
		FeaturedImage:   c.FeaturedImage,
		Price:           c.Price,
		MinPrice:        c.MinPrice,
		Province:        c.Province,
		Ward:            c.Ward,
		Road:            c.Road,
		RoadDisplay:     c.RoadDisplay,
		HasHouse:        c.HasHouse,
		ResidentialType: c.ResidentialType,
		ResidentialArea: c.ResidentialArea,
		AreaDimensions:  c.AreaDimensions,
		AreaRange:       c.AreaRange,
		FloorArea:       c.FloorArea,
		FrontageActual:  c.FrontageActual,
		FrontageRange:   c.FrontageRange,
		SheetNumber:     c.SheetNumber,
		ParcelNumber:    c.ParcelNumber,
		ConsignerPhone:  c.ConsignerPhone,
		SellerPhone:     c.SellerPhone,
		Latitude:        c.Latitude,
		Longitude:       c.Longitude,
		SeoUrl:          c.SeoUrl,
		Status:          c.Status,
		DisplayOrder:    c.DisplayOrder,
		CreatedAt:       c.CreatedAt.Format("2006-01-02T15:04:05Z"),
		UpdatedAt:       c.UpdatedAt.Format("2006-01-02T15:04:05Z"),
	}

	// User name
	if c.User.Name != "" {
		doc.UserName = c.User.Name
	}

	// JSON fields — parse from raw JSON
	if c.LandDirections != nil {
		var directions interface{}
		if err := json.Unmarshal(c.LandDirections, &directions); err == nil {
			doc.LandDirections = directions
		}
	}

	if c.LandTypes != nil {
		var types interface{}
		if err := json.Unmarshal(c.LandTypes, &types); err == nil {
			doc.LandTypes = types
		}
	}

	if c.Images != nil {
		var images interface{}
		if err := json.Unmarshal(c.Images, &images); err == nil {
			doc.Images = images
		}
	}

	return doc
}
