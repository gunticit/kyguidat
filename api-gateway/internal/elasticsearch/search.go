package elasticsearch

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"strconv"
	"strings"
)

// SearchParams holds all search/filter parameters
type SearchParams struct {
	Search         string
	Province       string
	District       string
	Phone          string
	PropertyType   string
	HouseOnLand    string
	PriceRange     string
	ThoCu          string
	RoadType       string
	Frontage       string
	AreaRange      string
	FloorAreaRange string
	Direction      string
	SoTo           string
	SoThua         string
	Sort           string
	Page           int
	Limit          int
}

// SearchResult holds the search response
type SearchResult struct {
	Hits  []map[string]interface{} `json:"hits"`
	Total int64                    `json:"total"`
}

// SearchConsignments performs a full-text search with filters
func (c *Client) SearchConsignments(params SearchParams) (*SearchResult, error) {
	query := buildQuery(params)

	var buf bytes.Buffer
	if err := json.NewEncoder(&buf).Encode(query); err != nil {
		return nil, fmt.Errorf("error encoding query: %w", err)
	}

	from := (params.Page - 1) * params.Limit

	res, err := c.ES.Search(
		c.ES.Search.WithContext(context.Background()),
		c.ES.Search.WithIndex(IndexName),
		c.ES.Search.WithBody(&buf),
		c.ES.Search.WithFrom(from),
		c.ES.Search.WithSize(params.Limit),
		c.ES.Search.WithTrackTotalHits(true),
	)
	if err != nil {
		return nil, fmt.Errorf("search error: %w", err)
	}
	defer res.Body.Close()

	if res.IsError() {
		return nil, fmt.Errorf("search error: %s", res.String())
	}

	var result map[string]interface{}
	if err := json.NewDecoder(res.Body).Decode(&result); err != nil {
		return nil, fmt.Errorf("error parsing response: %w", err)
	}

	// Extract hits
	hits := result["hits"].(map[string]interface{})
	totalObj := hits["total"].(map[string]interface{})
	total := int64(totalObj["value"].(float64))

	var documents []map[string]interface{}
	hitsList := hits["hits"].([]interface{})
	for _, hit := range hitsList {
		h := hit.(map[string]interface{})
		source := h["_source"].(map[string]interface{})
		documents = append(documents, source)
	}

	return &SearchResult{
		Hits:  documents,
		Total: total,
	}, nil
}

// buildQuery constructs the ES query DSL
func buildQuery(params SearchParams) map[string]interface{} {
	must := []map[string]interface{}{}
	filter := []map[string]interface{}{}

	// Always filter by approved/selling status
	filter = append(filter, map[string]interface{}{
		"terms": map[string]interface{}{
			"status": []string{"approved", "selling"},
		},
	})

	// Full-text search
	if params.Search != "" {
		must = append(must, map[string]interface{}{
			"multi_match": map[string]interface{}{
				"query":     params.Search,
				"fields":    []string{"title^3", "address^2", "description", "keywords^2", "code^4"},
				"type":      "best_fields",
				"fuzziness": "AUTO",
			},
		})
	}

	// Province filter
	if params.Province != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"province": params.Province,
			},
		})
	}

	// District/ward filter
	if params.District != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"ward": params.District,
			},
		})
	}

	// Phone filter
	if params.Phone != "" {
		must = append(must, map[string]interface{}{
			"bool": map[string]interface{}{
				"should": []map[string]interface{}{
					{"wildcard": map[string]interface{}{"consigner_phone": map[string]interface{}{"value": "*" + params.Phone + "*"}}},
					{"wildcard": map[string]interface{}{"seller_phone": map[string]interface{}{"value": "*" + params.Phone + "*"}}},
					{"term": map[string]interface{}{"order_number": params.Phone}},
				},
				"minimum_should_match": 1,
			},
		})
	}

	// Property type filter (land_types JSON array)
	if params.PropertyType != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"land_types": params.PropertyType,
			},
		})
	}

	// House on land
	if params.HouseOnLand != "" {
		val := "no"
		if params.HouseOnLand == "co" {
			val = "yes"
		}
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"has_house": val,
			},
		})
	}

	// Price range (e.g. "500-1000", "5000+")
	if params.PriceRange != "" {
		priceFilter := parsePriceRange(params.PriceRange)
		if priceFilter != nil {
			filter = append(filter, priceFilter)
		}
	}

	// Residential type (tho_cu)
	if params.ThoCu != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"residential_type": params.ThoCu,
			},
		})
	}

	// Road type
	if params.RoadType != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"road_display": params.RoadType,
			},
		})
	}

	// Frontage range
	if params.Frontage != "" {
		frontageFilter := parseRangeFilter("frontage_actual", params.Frontage)
		if frontageFilter != nil {
			filter = append(filter, frontageFilter)
		}
	}

	// Area range
	if params.AreaRange != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"area_range": params.AreaRange,
			},
		})
	}

	// Floor area range
	if params.FloorAreaRange != "" {
		floorFilter := parseRangeFilter("floor_area", params.FloorAreaRange)
		if floorFilter != nil {
			filter = append(filter, floorFilter)
		}
	}

	// Direction (land_directions JSON array)
	if params.Direction != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"land_directions": params.Direction,
			},
		})
	}

	// Sheet number (so_to)
	if params.SoTo != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"sheet_number": params.SoTo,
			},
		})
	}

	// Parcel number (so_thua)
	if params.SoThua != "" {
		filter = append(filter, map[string]interface{}{
			"term": map[string]interface{}{
				"parcel_number": params.SoThua,
			},
		})
	}

	// Build the bool query
	boolQuery := map[string]interface{}{}
	if len(must) > 0 {
		boolQuery["must"] = must
	}
	if len(filter) > 0 {
		boolQuery["filter"] = filter
	}
	if len(must) == 0 && len(filter) > 0 {
		// No search text but have filters — match all with filters
		boolQuery["must"] = []map[string]interface{}{
			{"match_all": map[string]interface{}{}},
		}
	}

	query := map[string]interface{}{
		"query": map[string]interface{}{
			"bool": boolQuery,
		},
	}

	// Sorting
	query["sort"] = buildSort(params.Sort, params.Search != "")

	return query
}

// buildSort returns the sort clause
func buildSort(sort string, hasSearch bool) []map[string]interface{} {
	switch sort {
	case "newest":
		return []map[string]interface{}{
			{"created_at": map[string]interface{}{"order": "desc"}},
		}
	case "oldest":
		return []map[string]interface{}{
			{"created_at": map[string]interface{}{"order": "asc"}},
		}
	case "price_asc":
		return []map[string]interface{}{
			{"price": map[string]interface{}{"order": "asc"}},
		}
	case "price_desc":
		return []map[string]interface{}{
			{"price": map[string]interface{}{"order": "desc"}},
		}
	case "area_asc":
		return []map[string]interface{}{
			{"residential_area": map[string]interface{}{"order": "asc"}},
		}
	case "area_desc":
		return []map[string]interface{}{
			{"residential_area": map[string]interface{}{"order": "desc"}},
		}
	default:
		if hasSearch {
			// Sort by relevance score when searching
			return []map[string]interface{}{
				{"_score": map[string]interface{}{"order": "desc"}},
				{"display_order": map[string]interface{}{"order": "asc", "missing": "_last"}},
				{"created_at": map[string]interface{}{"order": "desc"}},
			}
		}
		// Default sort: display_order then newest
		return []map[string]interface{}{
			{"display_order": map[string]interface{}{"order": "asc", "missing": "_last"}},
			{"created_at": map[string]interface{}{"order": "desc"}},
		}
	}
}

// parsePriceRange parses "500-1000" or "5000+" format (values in millions)
func parsePriceRange(pr string) map[string]interface{} {
	if strings.HasSuffix(pr, "+") {
		minStr := strings.TrimSuffix(pr, "+")
		min, err := strconv.ParseFloat(minStr, 64)
		if err != nil {
			return nil
		}
		return map[string]interface{}{
			"range": map[string]interface{}{
				"price": map[string]interface{}{
					"gte": min * 1000000,
				},
			},
		}
	}

	if strings.Contains(pr, "-") {
		parts := strings.SplitN(pr, "-", 2)
		min, err1 := strconv.ParseFloat(parts[0], 64)
		max, err2 := strconv.ParseFloat(parts[1], 64)
		if err1 != nil || err2 != nil {
			return nil
		}
		return map[string]interface{}{
			"range": map[string]interface{}{
				"price": map[string]interface{}{
					"gte": min * 1000000,
					"lte": max * 1000000,
				},
			},
		}
	}

	return nil
}

// parseRangeFilter parses "5-10" or "20+" format for numeric fields
func parseRangeFilter(field, value string) map[string]interface{} {
	if strings.HasSuffix(value, "+") {
		minStr := strings.TrimSuffix(value, "+")
		min, err := strconv.ParseFloat(minStr, 64)
		if err != nil {
			return nil
		}
		return map[string]interface{}{
			"range": map[string]interface{}{
				field: map[string]interface{}{
					"gte": min,
				},
			},
		}
	}

	if strings.Contains(value, "-") {
		parts := strings.SplitN(value, "-", 2)
		min, err1 := strconv.ParseFloat(parts[0], 64)
		max, err2 := strconv.ParseFloat(parts[1], 64)
		if err1 != nil || err2 != nil {
			return nil
		}
		return map[string]interface{}{
			"range": map[string]interface{}{
				field: map[string]interface{}{
					"gte": min,
					"lte": max,
				},
			},
		}
	}

	return nil
}
