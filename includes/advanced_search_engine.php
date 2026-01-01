<?php
/**
 * Advanced Search Engine for Islamic Content
 * Provides comprehensive search and filtering capabilities
 */

class AdvancedSearchEngine {
    private $api;
    
    public function __construct($api) {
        $this->api = $api;
    }
    
    /**
     * Search content with advanced filters
     */
    public function search($query, $contentType, $filters = []) {
        $results = [];
        
        switch ($contentType) {
            case 'asmaul_husna':
                $results = $this->searchAsmaulHusna($query, $filters);
                break;
            case 'doa':
                $results = $this->searchDoa($query, $filters);
                break;
            case 'hadits':
                $results = $this->searchHadits($query, $filters);
                break;
        }
        
        return $results;
    }
    
    /**
     * Search Asmaul Husna with fuzzy matching
     */
    private function searchAsmaulHusna($query, $filters = []) {
        try {
            $allData = $this->api->getAllAsmaulHusna();
            if (!isset($allData['data']) || !is_array($allData['data'])) {
                return ['data' => [], 'total' => 0];
            }
            
            $results = [];
            $queryLower = strtolower(trim($query));
            
            foreach ($allData['data'] as $asma) {
                $score = $this->calculateAsmaulHusnaScore($queryLower, $asma);
                
                // Apply filters
                if (!empty($filters['number_range'])) {
                    $range = explode('-', $filters['number_range']);
                    if (count($range) === 2) {
                        $min = (int)$range[0];
                        $max = (int)$range[1];
                        $asmaId = isset($asma['id']) ? $asma['id'] : 0;
                        if ($asmaId < $min || $asmaId > $max) {
                            continue;
                        }
                    }
                }
                
                if ($score >= 30) { // Minimum relevance threshold
                    $results[] = [
                        'data' => $asma,
                        'score' => $score,
                        'highlights' => $this->highlightMatches($asma, $queryLower)
                    ];
                }
            }
            
            // Sort by relevance score
            usort($results, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            return [
                'data' => $results,
                'total' => count($results),
                'query' => $query,
                'search_time' => microtime(true)
            ];
            
        } catch (Exception $e) {
            error_log("Asmaul Husna search error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Search Doa with category and content filtering
     */
    private function searchDoa($query, $filters = []) {
        try {
            $results = [];
            $queryLower = strtolower(trim($query));
            
            if (empty($queryLower)) {
                return ['data' => [], 'total' => 0, 'error' => 'Query kosong'];
            }
            
            // Search through all doa (1-108)
            for ($i = 1; $i <= 108; $i++) {
                try {
                    $doaData = $this->api->getDoa($i);
                    if (isset($doaData['data'])) {
                        $score = $this->calculateDoaScore($queryLower, $doaData['data']);
                        
                        // Apply category filter
                        if (!empty($filters['category'])) {
                            $category = $this->getDoaCategory($i);
                            if ($category !== $filters['category']) {
                                continue;
                            }
                        }
                        
                        if ($score >= 20) { // Lower threshold for better results
                            $doaData['data']['id'] = $i; // Ensure ID is set
                            $doaData['data']['category'] = $this->getDoaCategory($i);
                            
                            $results[] = [
                                'data' => $doaData['data'],
                                'score' => $score,
                                'highlights' => $this->highlightMatches($doaData['data'], $queryLower),
                                'category' => $this->getDoaCategory($i)
                            ];
                        }
                    }
                } catch (Exception $e) {
                    // Log individual doa errors but continue
                    error_log("Error loading doa #$i: " . $e->getMessage());
                    continue;
                }
            }
            
            // Sort by relevance score
            usort($results, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            return [
                'data' => $results,
                'total' => count($results),
                'query' => $query,
                'search_time' => microtime(true)
            ];
            
        } catch (Exception $e) {
            error_log("Doa search error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Search Hadits across collections
     */
    private function searchHadits($query, $filters = []) {
        try {
            $results = [];
            $queryLower = strtolower(trim($query));
            
            // Define collections to search
            $collections = [
                'arbain' => ['max' => 42, 'method' => 'getHaditsArbain'],
                'bulughul_maram' => ['max' => 100, 'method' => 'getHaditsBulughulMaram'], // Limit for performance
            ];
            
            // Add perawi collections if specified
            if (!empty($filters['collection']) && $filters['collection'] === 'perawi') {
                $perawi_collections = ['bukhari', 'muslim', 'ahmad', 'tirmidzi', 'abudaud', 'nasai', 'ibnumajah'];
                foreach ($perawi_collections as $slug) {
                    if (empty($filters['perawi']) || $filters['perawi'] === $slug) {
                        $collections["perawi_{$slug}"] = ['max' => 50, 'method' => 'getHaditsPerawi', 'slug' => $slug];
                    }
                }
            } else {
                // Search specific collection
                if (!empty($filters['collection']) && isset($collections[$filters['collection']])) {
                    $collections = [$filters['collection'] => $collections[$filters['collection']]];
                }
            }
            
            foreach ($collections as $collectionKey => $config) {
                for ($i = 1; $i <= $config['max']; $i++) {
                    try {
                        if ($config['method'] === 'getHaditsPerawi') {
                            $haditsData = $this->api->getHaditsPerawi($config['slug'], $i);
                        } else {
                            $haditsData = $this->api->{$config['method']}($i);
                        }
                        
                        if (isset($haditsData['data'])) {
                            $score = $this->calculateHaditsScore($queryLower, $haditsData['data']);
                            
                            if ($score >= 30) {
                                $results[] = [
                                    'data' => $haditsData['data'],
                                    'score' => $score,
                                    'highlights' => $this->highlightMatches($haditsData['data'], $queryLower),
                                    'collection' => $collectionKey,
                                    'number' => $i
                                ];
                            }
                        }
                    } catch (Exception $e) {
                        // Skip individual hadits errors
                        continue;
                    }
                }
            }
            
            // Sort by relevance score
            usort($results, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            return [
                'data' => $results,
                'total' => count($results),
                'query' => $query,
                'search_time' => microtime(true)
            ];
            
        } catch (Exception $e) {
            error_log("Hadits search error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Calculate relevance score for Asmaul Husna
     */
    private function calculateAsmaulHusnaScore($query, $asma) {
        if (empty($query)) return 0;
        
        $score = 0;
        
        // Check Arabic name
        if (isset($asma['arab']) && stripos($asma['arab'], $query) !== false) {
            $score += 90;
        }
        
        // Check transliteration
        if (isset($asma['latin']) && stripos($asma['latin'], $query) !== false) {
            $score += 85;
        }
        
        // Check meaning/translation
        if (isset($asma['indo']) && stripos($asma['indo'], $query) !== false) {
            $score += 80;
        }
        
        // Fuzzy matching for transliteration
        if (isset($asma['latin'])) {
            $fuzzyScore = $this->fuzzyMatch($query, strtolower($asma['latin']));
            $score += $fuzzyScore * 0.6;
        }
        
        // Number match
        if (is_numeric($query) && isset($asma['id']) && $asma['id'] == (int)$query) {
            $score += 95;
        }
        
        return min($score, 100);
    }
    
    /**
     * Calculate relevance score for Doa
     */
    private function calculateDoaScore($query, $doa) {
        if (empty($query)) return 0;
        
        $score = 0;
        $query = strtolower($query);
        
        // Check title
        if (isset($doa['judul'])) {
            $judul = strtolower($doa['judul']);
            if (stripos($judul, $query) !== false) {
                $score += 90;
            }
            // Fuzzy matching for title
            $fuzzyScore = $this->fuzzyMatch($query, $judul);
            $score += $fuzzyScore * 0.5;
        }
        
        // Check Arabic text
        if (isset($doa['arab']) && stripos($doa['arab'], $query) !== false) {
            $score += 85;
        }
        
        // Check translation
        if (isset($doa['arti'])) {
            $arti = strtolower($doa['arti']);
            if (stripos($arti, $query) !== false) {
                $score += 80;
            }
            // Word-based matching for better results
            $words = explode(' ', $query);
            foreach ($words as $word) {
                if (strlen($word) > 2 && stripos($arti, $word) !== false) {
                    $score += 20;
                }
            }
        }
        
        // Check transliteration
        if (isset($doa['latin']) && stripos($doa['latin'], $query) !== false) {
            $score += 75;
        }
        
        // Check for common doa keywords
        $keywords = ['makan', 'tidur', 'perjalanan', 'perlindungan', 'rezeki', 'kesehatan', 'belajar', 'kerja', 'rumah'];
        foreach ($keywords as $keyword) {
            if (stripos($query, $keyword) !== false) {
                if (isset($doa['arti']) && stripos($doa['arti'], $keyword) !== false) {
                    $score += 30;
                }
                if (isset($doa['judul']) && stripos($doa['judul'], $keyword) !== false) {
                    $score += 40;
                }
            }
        }
        
        return min($score, 100);
    }
    
    /**
     * Calculate relevance score for Hadits
     */
    private function calculateHaditsScore($query, $hadits) {
        if (empty($query)) return 0;
        
        $score = 0;
        
        // Check Arabic text
        if (isset($hadits['arab']) && stripos($hadits['arab'], $query) !== false) {
            $score += 90;
        }
        
        // Check translation
        if (isset($hadits['arti']) && stripos($hadits['arti'], $query) !== false) {
            $score += 85;
        }
        
        // Check narrator
        if (isset($hadits['perawi']) && stripos($hadits['perawi'], $query) !== false) {
            $score += 80;
        }
        
        // Check theme/topic (if available)
        if (isset($hadits['tema']) && stripos($hadits['tema'], $query) !== false) {
            $score += 75;
        }
        
        return min($score, 100);
    }
    
    /**
     * Fuzzy string matching
     */
    private function fuzzyMatch($query, $target) {
        if (empty($query) || empty($target)) return 0;
        
        $queryLen = strlen($query);
        $targetLen = strlen($target);
        
        if ($queryLen === 0) return 0;
        if ($targetLen === 0) return 0;
        
        // Exact match
        if ($query === $target) return 100;
        
        // Substring match
        if (strpos($target, $query) !== false) {
            $position = strpos($target, $query);
            return 90 - ($position * 2);
        }
        
        // Character-based similarity
        $matches = 0;
        $queryChars = str_split($query);
        $targetChars = str_split($target);
        
        foreach ($queryChars as $char) {
            if (in_array($char, $targetChars)) {
                $matches++;
            }
        }
        
        return ($matches / $queryLen) * 60;
    }
    
    /**
     * Highlight search matches in content
     */
    private function highlightMatches($content, $query) {
        if (empty($query)) return $content;
        
        $highlighted = [];
        
        foreach ($content as $key => $value) {
            if (is_string($value) && stripos($value, $query) !== false) {
                $highlighted[$key] = preg_replace(
                    '/(' . preg_quote($query, '/') . ')/i',
                    '<mark class="bg-yellow-200 px-1 rounded">$1</mark>',
                    $value
                );
            } else {
                $highlighted[$key] = $value;
            }
        }
        
        return $highlighted;
    }
    
    /**
     * Get doa category based on ID
     */
    private function getDoaCategory($id) {
        if ($id >= 1 && $id <= 30) return 'harian';
        if ($id >= 31 && $id <= 60) return 'ibadah';
        if ($id >= 61 && $id <= 90) return 'perlindungan';
        if ($id >= 91 && $id <= 108) return 'khusus';
        return 'lainnya';
    }
    
    /**
     * Filter content by category
     */
    public function filterByCategory($content, $category) {
        return array_filter($content, function($item) use ($category) {
            return isset($item['category']) && $item['category'] === $category;
        });
    }
    
    /**
     * Get search suggestions
     */
    public function getSuggestions($query, $contentType, $limit = 5) {
        // This could be enhanced with a suggestion database
        $suggestions = [];
        
        switch ($contentType) {
            case 'asmaul_husna':
                $suggestions = ['Rahman', 'Rahim', 'Malik', 'Quddus', 'Salam'];
                break;
            case 'doa':
                $suggestions = ['makan', 'tidur', 'perjalanan', 'perlindungan', 'rezeki'];
                break;
            case 'hadits':
                $suggestions = ['iman', 'islam', 'ihsan', 'sholat', 'puasa'];
                break;
        }
        
        return array_slice($suggestions, 0, $limit);
    }
}