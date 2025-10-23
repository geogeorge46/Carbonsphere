# Carbonsphere Seller Dashboard

## Overview
The Seller Dashboard provides comprehensive product and order management functionality for sellers on the Carbonsphere platform.

## Features

### 1. Profile Section
- Display seller information (name, email, phone, account type)
- Edit profile and change password options

### 2. Product Management
- **View Products**: Table displaying all products with ID, image, name, price, stock, category, and status
- **Add Product**: Modal form with validation for:
  - Product name, description, price, stock quantity
  - Category selection, status, and image upload
- **Edit Product**: Modify existing product details
- **Delete Product**: Remove products with confirmation

### 3. Orders Management
- **View Orders**: Table showing customer orders with:
  - Order ID, customer name, product, quantity, total, status, date
- **Update Status**: Change order status (pending → processed → shipped → delivered)
- **View Details**: Access full order information

### 4. Analytics Dashboard
- **Real-time Stats**: Total products, orders, revenue, pending orders
- **Visual Cards**: Modern design with icons and hover effects

## Database Schema

### Tables Created:
- `products`: Product catalog with seller relationships
- `orders`: Customer orders linked to products and users
- `categories`: Predefined product categories
- `product_images`: Multiple images per product
- `user_profiles`: Extended seller information

## Setup Instructions

### 1. Database Setup
Run the schema file to create necessary tables:
```bash
# Access via browser or command line
http://localhost/Ecosphere/run_schema.php
```

### 2. File Permissions
Ensure the uploads directory is writable:
```bash
chmod 755 uploads/
chmod 755 uploads/products/
```

### 3. Access Dashboard
- Login as a seller account
- Dashboard automatically redirects sellers to the management interface

## Security Features

- **Session Security**: Regenerate session ID to prevent fixation
- **Role-based Access**: Only sellers can access the dashboard
- **Input Validation**: Server-side validation for all forms
- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Protection**: Input sanitization and output escaping

## File Structure

```
Ecosphere/
├── dashboard.php          # Main seller dashboard
├── Product.php            # Product management class
├── Order.php              # Order management class
├── get_product.php        # API for product editing
├── product_schema.sql     # Database schema
├── run_schema.php         # Schema execution script
├── uploads/products/      # Product image storage
└── README_SELLER_DASHBOARD.md
```

## Usage

### Adding Products
1. Click "Add New Product" button
2. Fill in all required fields
3. Upload product image (optional)
4. Click "Save Product"

### Managing Orders
1. View orders in the Orders section
2. Click edit button to update status
3. Select new status from available options

### Editing Products
1. Click edit icon in products table
2. Modify details in the modal
3. Save changes

## Technical Details

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+, MySQL 5.7+
- **Styling**: Custom CSS with gradients and animations
- **Responsive**: Mobile-first design approach

## Browser Support
- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

## Troubleshooting

### Database Connection Issues
- Verify MySQL credentials in `Database.php`
- Ensure database exists and is accessible

### Image Upload Problems
- Check file permissions on uploads directory
- Verify PHP upload settings in php.ini

### Permission Errors
- Ensure user is logged in with seller role
- Check session variables are set correctly

## Future Enhancements

- [ ] Bulk product operations
- [ ] Advanced analytics with charts
- [ ] Email notifications for orders
- [ ] Inventory management alerts
- [ ] Product variants (size, color)
- [ ] Customer communication system</content>
</xai:function_call">Created new file: c:\xampp\htdocs\Ecosphere\README_SELLER_DASHBOARD.md