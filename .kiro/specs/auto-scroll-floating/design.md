# Design Document: Auto Scroll Floating Button

## Overview

Fitur tombol auto scroll floating adalah komponen UI yang memungkinkan pengguna untuk melakukan scroll otomatis pada halaman Al-Quran dengan kontrol kecepatan dan arah yang dapat disesuaikan. Fitur ini dirancang untuk meningkatkan pengalaman membaca dengan memberikan kemudahan navigasi yang intuitif dan tidak mengganggu.

## Architecture

### Component Structure
```
AutoScrollController
├── FloatingButton (Main UI Component)
├── SpeedControl (Speed adjustment)
├── DirectionControl (Up/Down direction)
├── ScrollEngine (Core scrolling logic)
└── SettingsManager (Persistence layer)
```

### Integration Points
- **Al-Quran Display Component**: Terintegrasi dengan `partials/alquran_display.php`
- **Existing JavaScript**: Bekerja bersama dengan font size controls dan copy functionality
- **Local Storage**: Menyimpan preferensi pengguna
- **CSS Framework**: Menggunakan Tailwind CSS yang sudah ada

## Components and Interfaces

### 1. FloatingButton Component

**Location**: Fixed position di kanan bawah layar
**Structure**:
```html
<div id="auto-scroll-floating" class="fixed bottom-6 right-6 z-50">
  <div class="bg-white rounded-full shadow-lg border border-gray-200">
    <!-- Main scroll button -->
    <!-- Speed controls -->
    <!-- Direction controls -->
  </div>
</div>
```

**States**:
- `inactive`: Tombol utama terlihat, kontrol tersembunyi
- `active`: Semua kontrol terlihat, scrolling aktif
- `expanded`: Menu kontrol terbuka untuk pengaturan

### 2. ScrollEngine

**Core Functions**:
```javascript
class ScrollEngine {
  constructor() {
    this.isActive = false;
    this.speed = 'medium'; // slow, medium, fast
    this.direction = 'down'; // up, down
    this.intervalId = null;
  }
  
  start() { /* Start auto scrolling */ }
  stop() { /* Stop auto scrolling */ }
  setSpeed(speed) { /* Change scroll speed */ }
  setDirection(direction) { /* Change scroll direction */ }
}
```

**Speed Settings**:
- **Slow**: 1px per 50ms (20px/second)
- **Medium**: 1px per 30ms (33px/second)  
- **Fast**: 1px per 20ms (50px/second)

### 3. SettingsManager

**Persistence Functions**:
```javascript
class SettingsManager {
  saveSettings(settings) { /* Save to localStorage */ }
  loadSettings() { /* Load from localStorage */ }
  resetSettings() { /* Reset to defaults */ }
}
```

**Default Settings**:
```javascript
const DEFAULT_SETTINGS = {
  speed: 'medium',
  direction: 'down',
  autoStart: false
};
```

## Data Models

### Settings Object
```javascript
{
  speed: 'slow' | 'medium' | 'fast',
  direction: 'up' | 'down',
  autoStart: boolean,
  lastUsed: timestamp
}
```

### Scroll State
```javascript
{
  isActive: boolean,
  currentSpeed: number, // pixels per interval
  currentDirection: 'up' | 'down',
  intervalId: number | null,
  pausedByUser: boolean
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Floating Button Visibility
*For any* Al-Quran content page, the floating button should always be visible and accessible regardless of scroll position
**Validates: Requirements 1.1, 1.2, 1.3**

### Property 2: Auto Scroll State Consistency
*For any* auto scroll operation, the system state should correctly reflect whether scrolling is active or inactive
**Validates: Requirements 2.1, 2.3, 2.5**

### Property 3: Speed Control Responsiveness
*For any* speed change during active scrolling, the new speed should be applied immediately without stopping the scroll
**Validates: Requirements 3.2, 3.5**

### Property 4: Boundary Detection
*For any* auto scroll operation, when reaching page boundaries (top or bottom), the system should automatically stop
**Validates: Requirements 2.4, 4.4**

### Property 5: Settings Persistence
*For any* user preference change, the settings should be saved and restored correctly on page reload
**Validates: Requirements 7.1, 7.2, 7.3**

### Property 6: Manual Interaction Handling
*For any* manual user interaction (clicking, scrolling), auto scroll should pause and resume appropriately
**Validates: Requirements 6.3, 6.4**

### Property 7: Performance Consistency
*For any* device or browser, the auto scroll should maintain smooth performance without causing lag
**Validates: Requirements 6.1, 6.2, 6.5**

### Property 8: Integration Compatibility
*For any* existing page feature (font controls, copy buttons), the auto scroll should not interfere with their functionality
**Validates: Requirements 8.1, 8.2, 8.3, 8.4**

## Error Handling

### Scroll Boundary Errors
- **Detection**: Monitor `window.scrollY` and `document.body.scrollHeight`
- **Handling**: Auto-stop when boundaries reached
- **Recovery**: Allow restart from current position

### Performance Issues
- **Detection**: Monitor frame rate and scroll smoothness
- **Handling**: Automatically adjust speed if performance degrades
- **Fallback**: Disable auto scroll if critical performance issues detected

### Storage Errors
- **Detection**: Try-catch around localStorage operations
- **Handling**: Use in-memory storage as fallback
- **Recovery**: Graceful degradation without settings persistence

### Browser Compatibility
- **Detection**: Feature detection for required APIs
- **Handling**: Provide fallback implementations
- **Graceful Degradation**: Hide features not supported

## Testing Strategy

### Unit Tests
- Test individual component functions (start, stop, speed change)
- Test settings save/load functionality
- Test boundary detection logic
- Test performance monitoring

### Property-Based Tests
- Generate random scroll speeds and verify smooth operation
- Test with various page heights and content types
- Verify settings persistence across multiple sessions
- Test interaction with existing page features

### Integration Tests
- Test with actual Al-Quran content pages
- Test on different devices and screen sizes
- Test with various browser configurations
- Test accessibility with keyboard navigation

### Performance Tests
- Measure scroll smoothness across different devices
- Test memory usage during extended scrolling
- Verify no interference with existing page performance
- Test battery usage on mobile devices

## Implementation Notes

### CSS Considerations
- Use `transform` instead of changing `scrollTop` for better performance
- Implement smooth transitions for button state changes
- Ensure proper z-index layering
- Responsive design for mobile devices

### JavaScript Optimization
- Use `requestAnimationFrame` for smooth scrolling
- Implement throttling for user interaction detection
- Minimize DOM queries with caching
- Use event delegation for better performance

### Accessibility
- Provide keyboard shortcuts (Space to toggle, +/- for speed)
- Include ARIA labels for screen readers
- Ensure sufficient color contrast
- Support high contrast mode

### Mobile Considerations
- Touch-friendly button sizes (minimum 44px)
- Prevent accidental activation
- Handle orientation changes
- Optimize for touch interactions