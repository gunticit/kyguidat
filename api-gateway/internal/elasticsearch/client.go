package elasticsearch

import (
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"time"
)

// Client wraps a lightweight HTTP-based Elasticsearch client
type Client struct {
	BaseURL    string
	HTTPClient *http.Client
}

// NewClient creates a new Elasticsearch client using net/http
func NewClient() (*Client, error) {
	esURL := os.Getenv("ELASTICSEARCH_URL")
	if esURL == "" {
		esURL = "http://elasticsearch:9200"
	}

	client := &Client{
		BaseURL: esURL,
		HTTPClient: &http.Client{
			Timeout: 30 * time.Second,
		},
	}

	// Check connection
	resp, err := client.HTTPClient.Get(esURL)
	if err != nil {
		return nil, fmt.Errorf("error connecting to ES: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		body, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("ES connection error: %s", string(body))
	}

	log.Println("✅ Connected to Elasticsearch")
	return client, nil
}

// Health checks cluster health
func (c *Client) Health() (string, error) {
	resp, err := c.HTTPClient.Get(c.BaseURL + "/_cluster/health")
	if err != nil {
		return "", err
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return "", err
	}

	if resp.StatusCode != 200 {
		return "", fmt.Errorf("cluster health error: %s", string(body))
	}

	return string(body), nil
}

// doRequest performs an HTTP request to ES with application/json
func (c *Client) doRequest(method, path string, body io.Reader) ([]byte, int, error) {
	return c.doRequestWithContentType(method, path, body, "application/json")
}

// doRequestWithContentType performs an HTTP request with a custom content type
func (c *Client) doRequestWithContentType(method, path string, body io.Reader, contentType string) ([]byte, int, error) {
	req, err := http.NewRequest(method, c.BaseURL+path, body)
	if err != nil {
		return nil, 0, err
	}
	req.Header.Set("Content-Type", contentType)

	resp, err := c.HTTPClient.Do(req)
	if err != nil {
		return nil, 0, err
	}
	defer resp.Body.Close()

	respBody, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, resp.StatusCode, err
	}

	return respBody, resp.StatusCode, nil
}
