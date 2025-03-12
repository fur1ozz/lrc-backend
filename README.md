# 🚀 Admin Panel - Powered by Filament

This project uses **Filament** as the admin panel for managing resources, users, and other backend operations.

## 🌐 Access the Admin Panel
By default, the admin panel is accessible at:

```
http://your-app.test/admin
```

Log in using the admin credentials you created.

## 🔄 Updating Filament
To keep Filament up to date, run the following commands:

```bash
composer update filament/filament
php artisan filament:upgrade
```

## 📂 Creating a New Resource
To generate a new Filament resource (e.g., for managing users):

```bash
php artisan make:filament-resource User
```

Edit the generated file in:

```
app/Filament/Resources/UserResource.php
```

## 📌 Customizing Filament
- **Change Admin Path:** Edit the `path` in `config/filament.php`:

```php
'path' => 'admin',
```
