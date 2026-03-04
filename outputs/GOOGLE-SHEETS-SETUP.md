# Google Sheets Setup Guide

This guide will walk you through setting up Google Sheets integration for Yuni Legend.

## Prerequisites

- Google Account
- Access to Google Cloud Console
- Project cloned and dependencies installed

## Step-by-Step Setup

### Part 1: Google Cloud Console Setup

#### 1. Create a New Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown (top left, next to "Google Cloud")
3. Click **"New Project"**
4. Enter project details:
   - **Project name**: `Yuni-Legend` (or any name you prefer)
   - **Organization**: Leave as default or select your organization
5. Click **"Create"**
6. Wait for the project to be created (this may take a few seconds)
7. Select your newly created project from the dropdown

#### 2. Enable Google Sheets API

1. In the left sidebar, click **"APIs & Services"** → **"Library"**
2. Search for **"Google Sheets API"**
3. Click on **"Google Sheets API"** in the results
4. Click the **"Enable"** button
5. Wait for the API to be enabled (you'll see a dashboard page)

### Part 2: Service Account Creation

#### 1. Create Service Account

1. Go to **"APIs & Services"** → **"Credentials"**
2. Click **"Create Credentials"** → **"Service Account"**
3. Fill in the form:
   - **Service account name**: `yuni-sheets-service`
   - **Service account ID**: (auto-generated, e.g., `yuni-sheets-service`)
   - **Description**: `Service account for Yuni Legend Google Sheets integration`
4. Click **"Create and Continue"**

#### 2. Grant Permissions

1. **Select a role**: Choose **"Editor"** from the list
   - This gives the service account permission to read and write
2. Click **"Continue"**
3. Skip the "Grant users access to this service account" section
4. Click **"Done"**

#### 3. Create and Download Key

1. In the **Credentials** page, find your service account in the list
2. Click on the service account name (e.g., `yuni-sheets-service@...`)
3. Go to the **"Keys"** tab
4. Click **"Add Key"** → **"Create new key"**
5. Select **"JSON"** format
6. Click **"Create"**
7. A JSON file will automatically download to your computer
   - Default name: `your-project-name-xxxxxxxxxxxxx.json`

### Part 3: Configure Your Application

#### 1. Move the Credentials File

```bash
# Navigate to your project directory
cd /path/to/yuni-legend

# Copy the downloaded file to the correct location
# Replace 'Downloads' with your actual download folder path
# Replace 'your-project-xxxxx.json' with your actual filename
cp ~/Downloads/your-project-xxxxx.json storage/app/google-auth.json
```

**⚠️ Important Security Notes:**
- Never commit this file to Git
- Never share this file publicly
- Keep it secure on your server
- The file is already added to `.gitignore`

#### 2. Verify the File Structure

Open `storage/app/google-auth.json` and verify it looks like this:

```json
{
  "type": "service_account",
  "project_id": "your-project-id",
  "private_key_id": "xxxxx",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "yuni-sheets-service@your-project.iam.gserviceaccount.com",
  "client_id": "xxxxx",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "...",
  "universe_domain": "googleapis.com"
}
```

### Part 4: Configure Google Sheet Access

#### 1. Get the Service Account Email

Open `storage/app/google-auth.json` and find the `client_email` field:

```json
"client_email": "yuni-sheets-service@your-project.iam.gserviceaccount.com"
```

Copy this email address.

#### 2. Create or Open Your Google Sheet

1. Go to [Google Sheets](https://sheets.google.com)
2. Either:
   - Create a new spreadsheet, or
   - Open an existing spreadsheet you want to use

#### 3. Share the Sheet with Service Account

1. Click the **"Share"** button (top right)
2. Paste the service account email (from step 1)
3. Set permission to **"Editor"**
4. **Uncheck** "Notify people" (service accounts don't need notifications)
5. Click **"Share"**

The service account now has access to your Google Sheet!

#### 4. Get the Spreadsheet ID

1. Look at your Google Sheet URL in the browser:
   ```
   https://docs.google.com/spreadsheets/d/1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4/edit
   ```

2. Copy the ID between `/d/` and `/edit`:
   ```
   1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4
   ```

### Part 5: Configure Environment Variables

#### 1. Open Your `.env` File

```bash
nano .env
# or use your preferred editor
```

#### 2. Add/Update Google Configuration

Add these lines (or update if they already exist):

```env
GOOGLE_SPREADSHEET_ID=1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4
GOOGLE_SERVICE_ACCOUNT_PATH=storage/app/google-auth.json
```

Replace `1ChEJ3RqMAVWOPyX7ibSOoc_quMiVDBK6A7rFCqP0Ig4` with your actual Spreadsheet ID.

#### 3. Clear Configuration Cache

```bash
php artisan config:clear
```

## Testing the Connection

### Test 1: Manual Test in Tinker

```bash
php artisan tinker
```

Then run:
```php
$service = new \App\Services\GoogleSheetService();
echo "Service initialized successfully!\n";

// Try to read from the sheet
$data = $service->readSheet('A1:A10');
print_r($data);
```

If successful, you'll see data from your sheet (or an empty array if sheet is empty).

### Test 2: Test Writing Data

```php
// Still in tinker
$service->appendRow(['Test', 'Data', 'Row']);
echo "Row added successfully!\n";
```

Check your Google Sheet - you should see a new row!

### Test 3: From Your Application

1. Login to the admin panel: `http://localhost:8000/admin`
2. Create or edit any record
3. Check your Google Sheet - it should sync automatically!

## Troubleshooting

### Error: "The caller does not have permission"

**Cause**: Service account doesn't have access to the sheet.

**Solution**: 
1. Double-check you shared the sheet with the correct service account email
2. Verify the permission is set to "Editor"
3. Try removing and re-adding the share

### Error: "Unable to parse range"

**Cause**: Sheet name has special characters or doesn't exist.

**Solution**:
1. Check the sheet name in your Google Sheet (tab name at bottom)
2. Ensure the sheet name in your code matches exactly
3. Sheet names with spaces or special characters need to be wrapped in quotes

### Error: "Service account file not found"

**Cause**: The credentials file is not in the correct location.

**Solution**:
```bash
# Check if file exists
ls -la storage/app/google-auth.json

# If not, copy it to the correct location
cp ~/Downloads/your-file.json storage/app/google-auth.json
```

### Error: "Invalid credentials"

**Cause**: The credentials file is corrupted or invalid.

**Solution**:
1. Download a new key from Google Cloud Console
2. Replace the old file
3. Clear config cache: `php artisan config:clear`

### Error: "Spreadsheet not found"

**Cause**: Invalid Spreadsheet ID in `.env`.

**Solution**:
1. Double-check the Spreadsheet ID from the URL
2. Ensure there are no extra spaces in `.env`
3. Clear config cache: `php artisan config:clear`

## Security Best Practices

### ✅ DO

- Keep `google-auth.json` secure
- Use environment variables for configuration
- Regularly rotate service account keys (every 90 days)
- Use separate service accounts for dev/staging/production
- Monitor API usage in Google Cloud Console

### ❌ DON'T

- Never commit `google-auth.json` to Git
- Never share credentials publicly
- Don't give service accounts more permissions than needed
- Don't hardcode credentials in code
- Don't use the same credentials across multiple projects

## Advanced Configuration

### Using Multiple Spreadsheets

If you need to work with multiple spreadsheets:

```env
GOOGLE_SPREADSHEET_ID_MAIN=xxxxx
GOOGLE_SPREADSHEET_ID_BACKUP=yyyyy
```

Then in your code:
```php
$service = new GoogleSheetService();
// Override spreadsheet ID for specific operations
$reflection = new \ReflectionClass($service);
$property = $reflection->getProperty('spreadsheetId');
$property->setAccessible(true);
$property->setValue($service, config('services.google.backup_spreadsheet_id'));
```

### Rate Limiting

Google Sheets API has quotas:
- 300 requests per 60 seconds per project
- 60 requests per 60 seconds per user

Monitor usage in Google Cloud Console:
1. Go to **APIs & Services** → **Dashboard**
2. Click on **Google Sheets API**
3. View **Metrics** tab

## Support

If you encounter issues:

1. Check the error logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. Enable debug mode in `.env`:
   ```env
   APP_DEBUG=true
   LOG_LEVEL=debug
   ```

3. Test the API directly using Google's OAuth Playground:
   - https://developers.google.com/oauthplayground/

4. Consult Google's official documentation:
   - https://developers.google.com/sheets/api/guides/concepts

---

**Next Steps:**
- Return to [README.md](README.md) for general usage
- Check [TODO-CHECKLIST.md](TODO-CHECKLIST.md) for additional tasks
- See [QUICK-START.md](QUICK-START.md) for a faster setup guide
