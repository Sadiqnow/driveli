# OCR Providers Setup Guide

This guide will help you configure OCR providers for the DriveLink driver verification system.

## Option 1: Google Vision API (Recommended)

Google Vision API provides excellent accuracy for document text extraction.

### Prerequisites
1. Google Cloud Platform account
2. Billing enabled on your GCP project
3. Vision API enabled

### Setup Steps

1. **Create a Google Cloud Project**
   ```bash
   # Install Google Cloud SDK
   curl https://sdk.cloud.google.com | bash
   exec -l $SHELL
   gcloud init
   ```

2. **Enable Vision API**
   ```bash
   gcloud services enable vision.googleapis.com
   ```

3. **Create Service Account**
   ```bash
   gcloud iam service-accounts create drivelink-ocr \
     --display-name="DriveLink OCR Service Account"
   
   # Grant necessary permissions
   gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
     --member="serviceAccount:drivelink-ocr@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
     --role="roles/vision.annotator"
   ```

4. **Generate Service Account Key**
   ```bash
   gcloud iam service-accounts keys create \
     storage/app/google-vision-credentials.json \
     --iam-account=drivelink-ocr@YOUR_PROJECT_ID.iam.gserviceaccount.com
   ```

5. **Update Environment Variables**
   ```env
   GOOGLE_CLOUD_PROJECT_ID=your-project-id
   GOOGLE_CLOUD_KEY_FILE=storage/app/google-vision-credentials.json
   OCR_PREFERRED_PROVIDER=google_vision
   GOOGLE_VISION_ENABLED=true
   ```

6. **Install Required Packages**
   ```bash
   composer require google/cloud-vision
   ```

## Option 2: AWS Textract

AWS Textract provides advanced document analysis capabilities.

### Prerequisites
1. AWS account with appropriate permissions
2. IAM user with Textract permissions

### Setup Steps

1. **Create IAM User**
   - Go to AWS IAM Console
   - Create user `drivelink-textract`
   - Attach policy `AmazonTextractFullAccess`

2. **Get Access Keys**
   - Generate access key and secret key for the user
   - Store securely

3. **Update Environment Variables**
   ```env
   AWS_ACCESS_KEY_ID=your-access-key-id
   AWS_SECRET_ACCESS_KEY=your-secret-access-key
   AWS_DEFAULT_REGION=us-east-1
   OCR_PREFERRED_PROVIDER=aws_textract
   AWS_TEXTRACT_ENABLED=true
   ```

4. **Install AWS SDK**
   ```bash
   composer require aws/aws-sdk-php
   ```

## Option 3: Tesseract OCR (Local)

Tesseract is a free, local OCR solution with good accuracy.

### Prerequisites
1. Tesseract OCR binary installed on server
2. Language packs for better accuracy

### Setup Steps

1. **Install Tesseract (Ubuntu/Debian)**
   ```bash
   sudo apt-get update
   sudo apt-get install tesseract-ocr
   sudo apt-get install tesseract-ocr-eng  # English language pack
   ```

2. **Install Tesseract (Windows with XAMPP)**
   - Download from: https://github.com/UB-Mannheim/tesseract/wiki
   - Install to `C:\Program Files\Tesseract-OCR`
   - Add to system PATH

3. **Install PHP Extension**
   ```bash
   composer require thiagoalessio/tesseract_ocr
   ```

4. **Update Environment Variables**
   ```env
   TESSERACT_PATH=C:\Program Files\Tesseract-OCR\tesseract.exe  # Windows
   # TESSERACT_PATH=/usr/bin/tesseract  # Linux
   TESSERACT_LANG=eng
   OCR_PREFERRED_PROVIDER=tesseract
   TESSERACT_ENABLED=true
   ```

5. **Test Installation**
   ```bash
   tesseract --version
   ```

## Configuration Priority

The system will use OCR providers in this order:
1. Google Vision API (if configured and enabled)
2. AWS Textract (if Google Vision fails or is disabled)
3. Tesseract OCR (fallback option)

## Testing OCR Setup

After configuration, test your OCR setup:

```bash
php artisan tinker
```

```php
// Test in Tinker
$ocrService = app(\App\Services\DocumentOCRService::class);
$result = $ocrService->extractDocumentData('path/to/test-image.jpg', 'nin');
dd($result);
```

## Performance Optimization

### Google Vision API
- Use batch processing for multiple documents
- Enable caching for repeated OCR requests
- Monitor API usage and costs

### AWS Textract
- Use asynchronous processing for large documents
- Leverage S3 integration for better performance
- Monitor AWS costs

### Tesseract OCR
- Preprocess images for better accuracy (resize, contrast adjustment)
- Use appropriate language packs
- Consider running on dedicated server for high volume

## Error Handling

The system includes automatic fallback:
- If primary provider fails → try secondary provider
- If all cloud providers fail → use Tesseract
- All failures are logged for debugging

## Security Considerations

1. **API Keys**: Store securely in environment variables
2. **Service Account Keys**: Restrict file permissions (600)
3. **Network**: Use HTTPS for all API calls
4. **Data**: Encrypt sensitive document images
5. **Logging**: Don't log API keys or sensitive data

## Cost Management

### Google Vision API
- $1.50 per 1,000 requests (first 1,000 free monthly)
- Monitor usage in Google Cloud Console

### AWS Textract
- $0.0015 per page for Detect Document Text
- Monitor usage in AWS Cost Explorer

### Tesseract OCR
- Free to use
- Only server resource costs (CPU, memory)

Choose the option that best fits your budget and accuracy requirements.