# Course Grid

**MyCustom Course Grid** is a WordPress plugin that creates a custom post type for MyCustom courses and displays them in a responsive card grid with pagination and advanced filtering options. It allows users to import at the moment LearnDash courses (`sfwd-courses`) into a custom `ld_course` post type and customize the grid's appearance through an admin settings interface.

## Features

- **Custom Post Type**: Manages MyCustom courses as `ld_course` posts with custom taxonomies and meta fields.
- **Responsive Card Grid**: Displays courses in a grid with customizable cards, including images, titles, categories, languages, prices, and more.
- **Advanced Filtering**: Supports filtering by category, tag, language, price range, and search term, with a user-friendly front-end interface.
- **Pagination**: Includes AJAX-powered pagination for seamless navigation.
- **Customizable Styling**: Admin settings to customize card background color, font color, title color, title alignment, button colors, title font, padding, content spacing, and content alignment.
- **Course Import**: Imports MyCustom `sfwd-courses` into the `ld_course` post type with options for default language and updating existing courses.
- **Multilingual Support**: Loads translations via the `ld-course-grid` text domain, with support for Greek and English.
- **Secure and Optimized**: Includes nonce verification for AJAX requests and sanitizes all user inputs.

## Requirements

- WordPress 5.0 or higher
- MyCustom LMS plugin (for `sfwd-courses` integration)
- PHP 7.4 or higher
- jQuery (included with WordPress)
- noUiSlider (loaded via CDN for price range slider)

## Installation

1. **Download the Plugin**:
   - Clone this repository or download the ZIP file:
     ```bash
     git clone https://github.com/miaouliswide/ld-course-grid.git
     ```
   - Alternatively, download the latest release from the [Releases](https://github.com/yourusername/ld-course-grid/releases) page.

2. **Upload to WordPress**:
   - Upload the `MyCustom-course-grid` folder to your WordPress site's `/wp-content/plugins/` directory.
   - Or, install the ZIP file via **Plugins > Add New > Upload Plugin** in the WordPress admin.

3. **Activate the Plugin**:
   - Go to **Plugins** in the WordPress admin and activate **MyCustom Course Grid**.

4. **Configure Settings**:
   - Navigate to **MyCustom Courses > Settings** to customize the grid's appearance (e.g., card colors, title alignment, spacing).
   - Optionally, go to **MyCustom Courses > Import SFWD Courses** to import existing MyCustom courses into the `ld_course` post type.

## Usage

### Displaying the Course Grid
To display the course grid on a page or post, use the following shortcode:
```html
[ld_course_grid]
```
This renders a responsive grid of course cards with filtering options and pagination.

### Customizing the Grid
1. **Admin Settings**:
   - Go to **MyCustom Courses > Settings** in the WordPress admin.
   - Adjust the following options:
     - **Card Background Color**: Set the background color of course cards.
     - **Card Font Color**: Set the text color for card content.
     - **Card Title Color**: Set the color of course titles.
     - **Button Background Color**: Set the background color of buttons.
     - **Button Text Color**: Set the text color of buttons.
     - **Card Title Font**: Choose from a list of fonts (e.g., Arial, Helvetica).
     - **Card Title Alignment**: Align the title (left, center, right).
     - **Card Padding**: Set the padding inside cards (e.g., `15px`).
     - **Card Content Spacing**: Set the spacing between title, category, and language (e.g., `5px`).
     - **Card Content Alignment**: Align card content (left, center, right).

2. **Importing Courses**:
   - Go to **MyCustom Courses > Import SFWD Courses**.
   - Select a default language (Greek or English) for imported courses.
   - Check **Update existing imported courses** to refresh existing `ld_course` posts.
   - Click **Import Courses** to start the import process.

### Filtering Courses
The front-end grid includes a filter form allowing users to:
- Filter by category, tag, or language using dropdowns.
- Set a price range using a slider (powered by noUiSlider).
- Search for courses by keyword.
- Reset filters to default.

## Development

### File Structure
```
MyCustom-course-grid/
├── assets/
│   ├── css/
│   │   └── ld-course-grid.css
│   ├── js/
│   │   ├── ld-course-grid.js
│   │   └── ld-course-grid-admin.js
├── includes/
│   ├── ajax.php
│   ├── meta-boxes.php
│   ├── post-types.php
│   ├── settings.php
│   ├── shortcode.php
│   ├── taxonomies.php
│   └── utilities.php
├── admin/
│   ├── admin-menus.php
│   └── import.php
├── languages/
├── ld-course-grid.php
└── README.md
```

### Key Files
- **ld-course-grid.php**: Main plugin file, initializes the plugin and enqueues assets.
- **includes/shortcode.php**: Defines the `[ld_course_grid]` shortcode to render the course grid.
- **includes/settings.php**: Registers admin settings for customizing the grid's appearance.
- **assets/css/ld-course-grid.css**: Styles the course grid and filter form.
- **assets/js/ld-course-grid.js**: Handles front-end AJAX for pagination and filtering.
- **admin/import.php**: Provides the interface for importing MyCustom courses.

### Contributing
Contributions are welcome! To contribute:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a Pull Request.

Please ensure your code follows WordPress coding standards and includes appropriate documentation.

## License

This plugin is licensed under the [GPLv2](https://www.gnu.org/licenses/gpl-2.0.html) or later. See the license details in `ld-course-grid.php`.

## Support

For issues, feature requests, or questions:
- Open an issue on the [GitHub Issues](https://github.com/yourusername/MyCustom-course-grid/issues) page.
- Contact the author, Miaoulis N, via the plugin’s support forum or GitHub.

## Changelog

### 7.1.1
- Added settings for card title color, title alignment, and content spacing.
- Updated CSS to support the `ld-course-card-title` class for course titles.
- Improved sanitization and validation for settings.

## Credits

- **Author**: Miaoulis N
- **Dependencies**: MyCustom LMS, jQuery, noUiSlider (CDN)

---

