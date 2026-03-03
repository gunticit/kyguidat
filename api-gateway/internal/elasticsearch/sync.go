package elasticsearch

import (
	"log"

	"khodat/api-gateway/internal/models"

	"gorm.io/gorm"
)

// Syncer handles MySQL → ES data synchronization
type Syncer struct {
	client *Client
	db     *gorm.DB
}

// NewSyncer creates a new sync service
func NewSyncer(client *Client, db *gorm.DB) *Syncer {
	return &Syncer{client: client, db: db}
}

// FullSync syncs all approved/selling consignments from MySQL to Elasticsearch
func (s *Syncer) FullSync() (int, error) {
	log.Println("🔄 Starting full sync MySQL → Elasticsearch...")

	// Ensure index exists
	if err := s.client.EnsureIndex(); err != nil {
		return 0, err
	}

	var consignments []models.Consignment
	err := s.db.
		Preload("User").
		Where("status IN (?)", []string{"approved", "selling"}).
		Find(&consignments).Error
	if err != nil {
		return 0, err
	}

	if len(consignments) == 0 {
		log.Println("⚠️  No consignments to sync")
		return 0, nil
	}

	// Bulk index in batches of 100
	batchSize := 100
	totalSynced := 0

	for i := 0; i < len(consignments); i += batchSize {
		end := i + batchSize
		if end > len(consignments) {
			end = len(consignments)
		}

		batch := consignments[i:end]
		if err := s.client.BulkIndex(batch); err != nil {
			log.Printf("⚠️  Error syncing batch %d-%d: %v", i, end, err)
			continue
		}
		totalSynced += len(batch)
	}

	log.Printf("✅ Full sync completed: %d consignments indexed", totalSynced)
	return totalSynced, nil
}

// SyncSingle syncs a single consignment by ID
func (s *Syncer) SyncSingle(id uint) error {
	var consignment models.Consignment
	err := s.db.Preload("User").First(&consignment, id).Error
	if err != nil {
		return err
	}

	// If approved/selling → index, otherwise delete from ES
	if consignment.Status == "approved" || consignment.Status == "selling" {
		return s.client.IndexConsignment(consignment)
	}

	return s.client.DeleteConsignment(id)
}

// RemoveFromIndex removes a consignment from ES
func (s *Syncer) RemoveFromIndex(id uint) error {
	return s.client.DeleteConsignment(id)
}
