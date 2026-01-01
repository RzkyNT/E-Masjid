# Design Document

## Introduction

This document outlines the design for implementing direct content display with advanced search functionality for Islamic content pages (Hadits, Doa, Asmaul Husna). The design transforms the current mode-selection interface into a direct content display similar to the Al-Quran page pattern.

## Overview

The system will be redesigned to display content immediately upon page load, eliminating the need for mode selection menus. Each page will feature comprehensive search and filtering capabilities, providing users with immediate access to Islamic content while maintaining excellent performance and usability.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Islamic Content Pages                    │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │   Hadits    │  │     Doa     │  │   Asmaul Husna      │  │
│  │   Page      │  │    Page     │  │      Page           │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│                 Direct Display Layer                        │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐ │
│  │            Advanced Search Engine                       │ │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────────┐   │ │
│  │  │   Fuzzy     │ │   Filter    │ │   Real-time     │   │ │
│  │  │  Matching   │ │   System    │ │   Search        │   │ │
│  │  └─────────────┘ └─────────────┘ └─────────────────┘   │ │
│  └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                    Data Access Layer                        │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────┐ │
│  │                MyQuran API                              │ │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────────┐   │ │
│  │  │   Caching   │ │ Rate Limit  │ │   Error         │   │ │
│  │  │   System    │ │   Manager   │ │   Handling      │   │ │
│  │  └─────────────┘ └─────────────┘ └─────────────────┘   │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Component Architecture

1. **Page Controllers**: Handle routing and parameter processing
2. **Content Display Engine**: Manages direct content rendering
3. **Search Engine**: Provides advanced search and filtering
4. **Data Access Layer**: Interfaces with MyQuran API
5. **UI Components**: Reusable interface elements

## Components and Interfaces

### 1. Direct Display Engine

**Purpose**: Manages immediate content display without mode selection

**Key Methods**:
```php
interface DirectDisplayInterface {
    public function getDefaultContent(): array;
    public function renderContentList(array $content, array $options = []): string;
    public function getPaginatedContent(int $page, int $limit): array;
}
```

**Implementation**:
- **HaditsDirectDisplay**: Shows collections list directly
- **DoaDirectDisplay**: Shows all 108 doa with categories
- **AsmaulHusnaDirectDisplay**: Shows all 99 names in grid/list format

### 2. Advanced Search Engine

**Purpose**: Provides comprehensive search and filtering capabilities

**Key Methods**:
```php
interface AdvancedSearchInterface {
    public function search(string $query, array $filters = []): array;
    public function fuzzyMatch(string $query, array $content): array;
    public function filterByCategory(array $content, string $category): array;
    public function highlightMatches(string $text, string $query): string;
}
```

**Search Capabilities**:
- **Text Search**: Arabic text, transliteration, translation
- **Category Filters**: By source, type, theme
- **Range Filters**: By number, date, length
- **Fuzzy Matching**: Tolerance for typos and variations

### 3. Content Renderers

**Enhanced Islamic Content Renderer**:
```php
class EnhancedIslamicContentRenderer extends IslamicContentRenderer {
    public function renderContentGrid(array $items, array $options = []): string;
    public function renderSearchResults(array $results, string $query): string;
    public function renderFilteredList(array $items, array $activeFilters): string;
}
```

### 4. Pagination System

**Purpose**: Handle large content sets efficiently

**Key Features**:
- Lazy loading for performance
- URL-based pagination state
- Search result pagination
- Infinite scroll option

## Data Models

### Search Query Model
```php
class SearchQuery {
    public string $query;
    public array $filters;
    public string $searchType; // 'all', 'arabic', 'translation', etc.
    public int $page;
    public int $limit;
    public string $sortBy;
    public string $sortOrder;
}
```

### Content Item Model
```php
class ContentItem {
    public int $id;
    public string $title;
    public string $arabicText;
    public string $transliteration;
    public string $translation;
    public string $category;
    public string $source;
    public array $metadata;
}
```

### Search Result Model
```php
class SearchResult {
    public array $items;
    public int $totalCount;
    public int $currentPage;
    public int $totalPages;
    public array $appliedFilters;
    public float $searchTime;
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property-Based Testing Analysis

Let me analyze the acceptance criteria to determine which can be tested as properties:

<function_calls>
<invoke name="prework">
<parameter name="featureName">islamic-content-direct-display

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Based on the prework analysis, here are the key correctness properties that must be validated:

### Property 1: Content Display Completeness
*For any* Islamic content page (Asmaul Husna, Doa, Hadits), when loaded, all expected content items should be present with required fields (number, Arabic text, transliteration, translation)
**Validates: Requirements 1.2, 2.2, 3.3**

### Property 2: Search Functionality Correctness
*For any* search query and content type, the search function should return only items that match the query criteria in the specified fields (Arabic, transliteration, translation, title)
**Validates: Requirements 4.1, 4.2, 4.3, 5.1, 5.2, 6.1**

### Property 3: Filter Consistency
*For any* filter criteria applied to content, all returned items should match the filter conditions and no matching items should be excluded
**Validates: Requirements 2.5, 4.4, 5.3, 5.4, 6.2, 6.3, 6.5**

### Property 4: Fuzzy Search Tolerance
*For any* search query with common typos or variations, the fuzzy search should return relevant results that would match the intended correct spelling
**Validates: Requirements 4.6**

### Property 5: Search Result Highlighting
*For any* search results displayed, all instances of the search query should be highlighted in the result text
**Validates: Requirements 4.5**

### Property 6: Category Organization
*For any* content with categories (Doa), all items should be properly grouped by their assigned categories and no items should appear in incorrect categories
**Validates: Requirements 2.3**

### Property 7: Click Interaction Behavior
*For any* clickable content item, clicking should display the complete details of that specific item
**Validates: Requirements 1.4, 2.4, 3.4**

### Property 8: Real-time Search Response
*For any* search input, results should update within the specified time limit as the user types
**Validates: Requirements 7.4**

### Property 9: Pagination Correctness
*For any* paginated content list, the total number of items across all pages should equal the total content count, and no items should be duplicated or missing
**Validates: Requirements 7.5**

### Property 10: Search State Persistence
*For any* navigation action during an active search, the search query and filters should be preserved and restored
**Validates: Requirements 7.6**

### Property 11: Performance Requirements
*For any* page load or search operation, the response time should be within the specified limits (2 seconds for content load, 1 second for search)
**Validates: Requirements 8.1, 8.2**

### Property 12: User Preference Persistence
*For any* user search preferences or settings, they should be saved and restored correctly across sessions
**Validates: Requirements 8.4**

### Property 13: Keyboard Navigation
*For any* keyboard shortcut defined for navigation, it should perform the correct action consistently
**Validates: Requirements 8.6**

## Error Handling

### Search Error Handling
- **Invalid Query**: Gracefully handle empty or malformed search queries
- **API Failures**: Provide fallback content when MyQuran API is unavailable
- **Network Timeouts**: Show appropriate error messages with retry options
- **Rate Limiting**: Queue requests and inform users of delays

### Content Loading Errors
- **Missing Data**: Display placeholder content with error indicators
- **Partial Failures**: Show available content while indicating missing parts
- **Cache Failures**: Fallback to direct API calls when cache is unavailable

### User Input Validation
- **Search Input Sanitization**: Prevent XSS and injection attacks
- **Filter Validation**: Ensure filter parameters are within valid ranges
- **Pagination Bounds**: Handle out-of-range page requests gracefully

## Testing Strategy

### Dual Testing Approach
The system will use both unit testing and property-based testing for comprehensive coverage:

**Unit Tests**: Focus on specific examples, edge cases, and error conditions
- Test specific search queries with known results
- Test filter combinations with expected outcomes
- Test error handling scenarios
- Test UI component rendering

**Property-Based Tests**: Verify universal properties across all inputs
- Generate random content sets and verify display completeness
- Generate random search queries and verify result accuracy
- Generate random filter combinations and verify consistency
- Test performance requirements with varying load conditions

### Property-Based Testing Configuration
- **Testing Framework**: PHPUnit with property-based testing extensions
- **Minimum Iterations**: 100 iterations per property test
- **Test Tags**: Each property test tagged with format: **Feature: islamic-content-direct-display, Property {number}: {property_text}**

### Integration Testing
- **API Integration**: Test MyQuran API integration with various scenarios
- **Cache Integration**: Test caching behavior under different conditions
- **Search Integration**: Test search engine with real content data
- **UI Integration**: Test complete user workflows end-to-end

### Performance Testing
- **Load Testing**: Test with maximum expected content volumes
- **Search Performance**: Measure search response times under load
- **Memory Usage**: Monitor memory consumption with large datasets
- **Cache Efficiency**: Measure cache hit rates and performance impact