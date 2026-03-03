package elasticsearch

import (
	"fmt"
	"log"
	"os"

	"github.com/elastic/go-elasticsearch/v8"
)

// Client wraps the Elasticsearch client
type Client struct {
	ES *elasticsearch.Client
}

// NewClient creates a new Elasticsearch client
func NewClient() (*Client, error) {
	esURL := os.Getenv("ELASTICSEARCH_URL")
	if esURL == "" {
		esURL = "http://elasticsearch:9200"
	}

	cfg := elasticsearch.Config{
		Addresses: []string{esURL},
	}

	es, err := elasticsearch.NewClient(cfg)
	if err != nil {
		return nil, fmt.Errorf("error creating ES client: %w", err)
	}

	// Check connection
	res, err := es.Info()
	if err != nil {
		return nil, fmt.Errorf("error connecting to ES: %w", err)
	}
	defer res.Body.Close()

	if res.IsError() {
		return nil, fmt.Errorf("ES connection error: %s", res.String())
	}

	log.Println("✅ Connected to Elasticsearch")
	return &Client{ES: es}, nil
}

// Health checks cluster health
func (c *Client) Health() (string, error) {
	res, err := c.ES.Cluster.Health()
	if err != nil {
		return "", err
	}
	defer res.Body.Close()

	if res.IsError() {
		return "", fmt.Errorf("cluster health error: %s", res.String())
	}

	return res.String(), nil
}
